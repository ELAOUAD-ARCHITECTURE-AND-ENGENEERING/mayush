<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\WebhookSignatureVerifier;
use Mayush\Shipping\Onessta\DTOs\WebhookPayloadDto;
use Mayush\Shipping\Onessta\Events\CodPaymentConfirmed;
use Mayush\Shipping\Onessta\Events\ShipmentInfoUpdated;
use Mayush\Shipping\Onessta\Events\ShipmentStatusUpdated;
use Mayush\Shipping\Onessta\Jobs\ProcessWebhookJob;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Models\OnesstaWebhookLog;

class WebhookService
{
    private WebhookSignatureVerifier $signatureVerifier;

    public function __construct(WebhookSignatureVerifier $signatureVerifier)
    {
        $this->signatureVerifier = $signatureVerifier;
    }

    public function handle(Request $request): OnesstaWebhookLog
    {
        $payload = $request->getContent();
        $headers = $request->headers;

        $log = OnesstaWebhookLog::create([
            'event_type' => $headers->get('X-Webhook-Event', 'unknown'),
            'header_api_key' => $headers->get('X-Api-Key'),
            'header_signature' => $headers->get('X-Signature'),
            'header_event' => $headers->get('X-Webhook-Event'),
            'payload' => $payload,
            'signature_valid' => null,
            'processed' => false,
        ]);

        try {
            $this->signatureVerifier->verify($payload, $headers->get('X-Signature'));
            $log->update(['signature_valid' => true]);
        } catch (\Throwable $e) {
            $log->update([
                'signature_valid' => false,
                'error_message' => $e->getMessage(),
            ]);

            if (config('onessta.webhook.fail_on_signature_mismatch')) {
                abort(403, 'Invalid signature');
            }
        }

        $useQueue = config('onessta.webhook.queue', true);

        if ($useQueue) {
            ProcessWebhookJob::dispatch($log);
        } else {
            $this->process($log);
        }

        return $log;
    }

    public function process(OnesstaWebhookLog $log): void
    {
        $payload = json_decode($log->payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $log->markAsFailed('Invalid JSON payload: ' . json_last_error_msg());
            return;
        }

        $dto = WebhookPayloadDto::fromArray($payload);

        if ($this->isDuplicate($dto)) {
            Log::info('ONESSTA: Duplicate webhook ignored', ['code' => $dto->code, 'event' => $dto->event]);
            $log->markAsProcessed();
            return;
        }

        try {
            $shipment = OnesstaShipment::byCode($dto->code)->first();

            if (!$shipment) {
                $log->markAsFailed("Shipment not found: {$dto->code}");
                return;
            }

            $previousStatus = $shipment->status;

            if ($dto->isStatusUpdate()) {
                $statusChanged = $previousStatus !== $dto->status;

                $shipment->update([
                    'status' => $dto->status,
                    'situation' => $dto->situation,
                    'payment_situation' => $this->resolvePaymentSituation($shipment, $dto->situation),
                    'synced_at' => now(),
                ]);

                if ($statusChanged) {
                    event(new ShipmentStatusUpdated($shipment, $previousStatus, $dto->status));
                }

                $this->handlePaymentConfirmation($shipment, $dto->situation);
            }

            if ($dto->isSecondStatusUpdate()) {
                $shipment->update(['status_second' => $dto->status]);
            }

            if ($dto->isInfoUpdate()) {
                $shipment->update([
                    'situation' => $dto->situation,
                    'reported_date' => $dto->timestamp ? now()->parse($dto->timestamp) : now(),
                    'synced_at' => now(),
                ]);
                event(new ShipmentInfoUpdated($shipment));
            }

            $log->update(['onessta_shipment_id' => $shipment->id]);
            $log->markAsProcessed();

        } catch (\Throwable $e) {
            Log::error('ONESSTA: Webhook processing failed', [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);
            $log->markAsFailed($e->getMessage());
        }
    }

    private function isDuplicate(WebhookPayloadDto $dto): bool
    {
        return OnesstaWebhookLog::processed()
            ->byEventType($dto->event)
            ->where('payload', $dto->raw)
            ->exists();
    }

    private function resolvePaymentSituation(OnesstaShipment $shipment, ?string $situation): string
    {
        if ($shipment->is_cod) {
            return match ($situation) {
                'PAID' => 'collected',
                'NOT_PAID' => 'cod_awaiting',
                default => $shipment->payment_situation ?? 'cod_awaiting',
            };
        }

        return $situation === 'PAID' ? 'paid' : ($shipment->payment_situation ?? 'unknown');
    }

    private function handlePaymentConfirmation(OnesstaShipment $shipment, ?string $situation): void
    {
        if ($shipment->is_cod && $situation === 'PAID') {
            $order = $shipment->order;
            if ($order && $order->payment_status !== 'paid') {
                $order->update([
                    'payment_status'  => 'paid',
                    'payment_details' => 'ONESSTA COD collected - Shipment: ' . $shipment->code,
                ]);

                Log::info('ONESSTA: COD payment confirmed via webhook', [
                    'order_id'      => $order->id,
                    'shipment_code' => $shipment->code,
                ]);

                // Fire a domain event so the host application can handle
                // commission calculation, buyer notification emails, and
                // loyalty credits without coupling this package to global helpers.
                event(new CodPaymentConfirmed($shipment, $order));
            }
        }
    }
}

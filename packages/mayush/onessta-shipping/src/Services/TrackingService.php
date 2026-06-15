<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\DTOs\TrackingEventDto;
use Mayush\Shipping\Onessta\DTOs\TrackingResponseDto;
use Mayush\Shipping\Onessta\Events\ShipmentStatusUpdated;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Models\OnesstaTrackingEvent;

class TrackingService
{
    private const CACHE_KEY_THROTTLE = 'onessta:tracking:throttle:';

    private OnesstaClient $client;

    public function __construct(OnesstaClient $client)
    {
        $this->client = $client;
    }

    public function track(string $code): TrackingResponseDto
    {
        $response = $this->client->post('/p/parcels/tracking', ['code' => $code]);

        if (!$response->successful()) {
            throw new \RuntimeException('Tracking API failed: ' . $response->status());
        }

        $raw = $response->json();
        $data = $raw['data']['parcel'] ?? $raw['data'] ?? $raw;

        return TrackingResponseDto::fromArray($data);
    }

    public function pollAndUpdate(string $code): void
    {
        $throttleKey = self::CACHE_KEY_THROTTLE . $code;
        $throttleMinutes = config('onessta.throttle.tracking_per_parcel_per_min', 1);

        if (Cache::has($throttleKey)) {
            Log::debug('ONESSTA: Tracking poll throttled', ['code' => $code]);
            return;
        }

        $trackingResponse = $this->track($code);
        $shipment = OnesstaShipment::byCode($code)->first();

        if (!$shipment) {
            Log::warning('ONESSTA: Shipment not found for tracking update', ['code' => $code]);
            return;
        }

        $previousStatus = $shipment->status;

        if ($trackingResponse->history) {
            $this->persistEvents($shipment, $trackingResponse->history);
        }

        $shipment->update([
            'status' => $trackingResponse->status,
            'situation' => $trackingResponse->situation,
            'synced_at' => now(),
        ]);

        Cache::put($throttleKey, true, now()->addMinutes($throttleMinutes));

        if ($previousStatus !== $trackingResponse->status) {
            event(new ShipmentStatusUpdated($shipment, $previousStatus, $trackingResponse->status));
        }
    }

    public function persistEvents(OnesstaShipment $shipment, $events): int
    {
        $count = 0;

        foreach ($events as $eventData) {
            $event = $eventData instanceof TrackingEventDto ? $eventData : TrackingEventDto::fromArray($eventData);

            $createdAt = $event->createdAt ? now()->parse($event->createdAt) : null;

            $created = OnesstaTrackingEvent::firstOrCreate(
                [
                    'onessta_shipment_id' => $shipment->id,
                    'status' => $event->status,
                    'created_at_remote' => $createdAt,
                ],
                [
                    'name' => $event->name,
                    'new_date' => $event->newDate ? now()->parse($event->newDate) : null,
                    'raw_payload' => $event->raw,
                ]
            );

            if ($created->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    public function toDeliveryStatus(string $onesstaStatus): string
    {
        return match ($onesstaStatus) {
            'WAITING_PICKUP' => 'pending',
            'PICKED_UP' => 'picked_up',
            'SENT' => 'on_the_way',
            'RECEIVED' => 'processing',
            'DISTRIBUTION' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RETURNING' => 'return_requested',
            'RETURNED' => 'refunded',
            'CANCELLED' => 'cancelled',
            default => 'pending',
        };
    }
}

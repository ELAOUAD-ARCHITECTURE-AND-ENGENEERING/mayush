<?php

namespace Mayush\Shipping\Onessta\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\Events\ShipmentCreated;
use Mayush\Shipping\Onessta\Events\ShipmentCreationFailed;
use Mayush\Shipping\Onessta\Exceptions\CityMappingException;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;
use Mayush\Shipping\Onessta\Services\ShipmentService;

class CreateShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public array $backoff;

    public function __construct(
        public readonly int $orderId,
        public readonly array $shipmentData
    ) {
        $this->tries = config('onessta.http.retry_times', 3);
        $this->backoff = config('onessta.queue.create_shipment_retry', [60, 300, 900]);
        $this->queue = config('onessta.queue.name', 'onessta');
    }

    public function handle(ShipmentService $shipmentService, ReferenceDataService $referenceDataService): void
    {
        Log::info('ONESSTA: CreateShipmentJob started', ['order_id' => $this->orderId]);

        try {
            $data = $this->shipmentData;

            if (isset($data['city_id'])) {
                $remoteCityId = $referenceDataService->resolveCity($data['city_id']);
                if ($remoteCityId) {
                    $data['city'] = $remoteCityId;
                } else {
                    throw new CityMappingException(
                        "No ONESSTA city mapping found for local city ID {$data['city_id']} (order #{$this->orderId}). "
                        . 'Run `php artisan onessta:sync-cities` and map the city in the admin panel.'
                    );
                }
            }

            if (empty($data['city'])) {
                throw new CityMappingException(
                    "Shipment for order #{$this->orderId} has no valid ONESSTA city ID or name. "
                    . 'Ensure the shipping address contains a mapped city_id or valid city name.'
                );
            }

            if (!is_numeric($data['city'])) {
                try {
                    $data['city'] = \Mayush\Shipping\Onessta\Helpers\CityMappingHelper::getRemoteCityId($data['city']);
                } catch (CityMappingException $e) {
                    throw new CityMappingException(
                        "No ONESSTA city mapping found for city name '{$data['city']}' (order #{$this->orderId}). "
                        . 'Run `php artisan onessta:sync-cities` and map the city in the admin panel.'
                    );
                }
            }

            $dto = new ShipmentRequestDto(
                code: $data['code'],
                receiver: $data['receiver'],
                phone: $data['phone'],
                price: $data['price'],
                city: $data['city'],
                address: $data['address'],
                sku: $data['sku'] ?? null,
                pickup_city: $data['pickup_city'] ?? null,
                note: $data['note'] ?? null,
                product_nature: $data['product_nature'] ?? null,
                can_open: $data['can_open'] ?? false,
                replace: $data['replace'] ?? false
            );

            $meta = [
                'is_cod' => $data['is_cod'] ?? false,
            ];

            $shipment = $shipmentService->createShipment($dto, $this->orderId, $meta);

            event(new ShipmentCreated($shipment));

            Log::info('ONESSTA: CreateShipmentJob completed', [
                'order_id' => $this->orderId,
                'shipment_code' => $shipment->code,
            ]);

        } catch (\Throwable $e) {
            Log::error('ONESSTA: CreateShipmentJob failed', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                event(new ShipmentCreationFailed($this->orderId, $e->getMessage()));
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ONESSTA: CreateShipmentJob permanently failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);

        event(new ShipmentCreationFailed($this->orderId, $exception->getMessage()));
    }
}

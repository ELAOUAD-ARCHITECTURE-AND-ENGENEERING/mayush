<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\DTOs\ShipmentResponseDto;
use Mayush\Shipping\Onessta\Events\ShipmentCreated;
use Mayush\Shipping\Onessta\Exceptions\CityMappingException;
use Mayush\Shipping\Onessta\Exceptions\ShipmentCreationException;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Models\OnesstaCityMap;

class ShipmentService
{
    private OnesstaClient $client;
    private ReferenceDataService $referenceDataService;

    public function __construct(OnesstaClient $client, ReferenceDataService $referenceDataService)
    {
        $this->client = $client;
        $this->referenceDataService = $referenceDataService;
    }

    public function createShipment(ShipmentRequestDto $dto, ?int $orderId = null, array $meta = []): OnesstaShipment
    {
        $rawRequest = $dto->toArray();

        try {
            $response = $this->client->post('/p/parcels/add', $rawRequest);
            $raw = $response->json();
            $data = $raw['data']['parcel'] ?? $raw['data'] ?? $raw;
            $dtoResponse = ShipmentResponseDto::fromArray($data);

            if (!$dtoResponse->isSuccess()) {
                throw new ShipmentCreationException(
                    'ONESSTA API returned error: ' . ($raw['message'] ?? $response->status()),
                    $orderId
                );
            }

            $shipment = $this->persistShipment($dtoResponse, $rawRequest, $raw, $orderId, $meta);

            event(new ShipmentCreated($shipment));

            Log::info('ONESSTA: Shipment created successfully', [
                'code' => $shipment->code,
                'order_id' => $orderId,
                'is_cod' => $meta['is_cod'] ?? false,
            ]);

            return $shipment;

        } catch (ShipmentCreationException $e) {
            Log::error('ONESSTA: Shipment creation failed', [
                'order_id' => $orderId,
                'code' => $dto->code,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('ONESSTA: Shipment creation failed', [
                'order_id' => $orderId,
                'code' => $dto->code,
                'error' => $e->getMessage(),
            ]);
            throw new ShipmentCreationException($e->getMessage(), $orderId, $e);
        }
    }

    public function fetchByCode(string $code): OnesstaShipment
    {
        $response = $this->client->post('/p/parcels/get_by_code', ['code' => $code]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch shipment: ' . $response->status());
        }

        $raw = $response->json();
        $data = $raw['data']['parcel'] ?? $raw['data'] ?? $raw;

        return $this->persistShipment(ShipmentResponseDto::fromArray($data), [], $raw);
    }

    private function persistShipment(
        ShipmentResponseDto $dto,
        array $rawRequest,
        array $rawResponse,
        ?int $orderId = null,
        array $meta = []
    ): OnesstaShipment {
        $cityId = $dto->getCityId();
        $cityName = is_array($dto->city ?? null) ? ($dto->city['name'] ?? null) : null;

        $situation = $rawResponse['data']['situation'] ?? $dto->situation ?? null;
        $paymentSituation = $situation === 'NOT_PAID' ? 'awaiting_payment' : ($meta['is_cod'] ?? false ? 'cod_awaiting' : 'paid');

        return OnesstaShipment::updateOrCreate(
            ['code' => $dto->code],
            [
                'order_id' => $orderId,
                'external_id' => $rawResponse['id'] ?? null,
                'receiver' => $dto->receiver ?? '',
                'phone' => $dto->phone ?? '',
                'address' => $dto->address ?? '',
                'city_id' => null,
                'city_name' => $cityName,
                'remote_city_id' => $cityId,
                'price' => $dto->price ?? 0,
                'status' => $dto->status,
                'situation' => $situation,
                'is_cod' => $meta['is_cod'] ?? false,
                'payment_situation' => $paymentSituation,
                'raw_request' => $rawRequest,
                'raw_response' => $rawResponse,
                'created_at_remote' => $dto->createdAt ? now()->parse($dto->createdAt) : null,
                'updated_at_remote' => $dto->updatedAt ? now()->parse($dto->updatedAt) : null,
                'synced_at' => now(),
            ]
        );
    }

    public function updateStatus(string $code, string $status, ?string $situation = null): OnesstaShipment
    {
        $shipment = OnesstaShipment::byCode($code)->first();

        if (!$shipment) {
            throw new \RuntimeException("Shipment not found: {$code}");
        }

        $shipment->update([
            'status' => $status,
            'situation' => $situation ?? $shipment->situation,
            'synced_at' => now(),
        ]);

        return $shipment->fresh();
    }
}

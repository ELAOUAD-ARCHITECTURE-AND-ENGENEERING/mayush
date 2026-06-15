<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Jobs\CreateShipmentJob;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;

class OrderShipmentDispatchService
{
    public function ensureForOrder($order, bool $force = false): array
    {
        if (!config('onessta.enabled', false)) {
            return $this->skip('disabled', 'ONESSTA integration is disabled.');
        }

        if (!addon_is_activated('onessta')) {
            return $this->skip('addon_disabled', 'ONESSTA addon is disabled.');
        }

        $shippingMethod = $this->getShippingMethod($order);
        $allowedMethods = ['onessta', 'home_delivery'];
        if (!in_array($shippingMethod, $allowedMethods) && !in_array($order->shipping_type, $allowedMethods)) {
            return $this->skip('shipping_method_not_supported', 'Order shipping method is not handled by ONESSTA.');
        }

        if ($order->delivery_status === 'cancelled') {
            Log::info('ONESSTA: Order cancelled, skipping shipment creation', [
                'order_id' => $order->id,
            ]);

            return $this->skip('cancelled', 'Order is cancelled.');
        }

        $existingShipment = OnesstaShipment::where('order_id', $order->id)->latest('id')->first();
        if ($existingShipment && !$force) {
            Log::info('ONESSTA: Shipment already exists for order, skipping', [
                'order_id' => $order->id,
                'shipment_code' => $existingShipment->code,
            ]);

            return [
                'status' => 'exists',
                'message' => 'ONESSTA shipment already exists.',
                'shipment_code' => $existingShipment->code,
            ];
        }

        $shipmentData = $this->buildShipmentData($order);
        $connection = config('onessta.queue.create_shipment_connection', config('onessta.queue.connection', config('queue.default')));
        $queue = config('onessta.queue.name', 'onessta');

        try {
            if ($connection === 'sync') {
                CreateShipmentJob::dispatchSync($order->id, $shipmentData);
                $shipment = OnesstaShipment::where('order_id', $order->id)->latest('id')->first();

                Log::info('ONESSTA: Shipment created synchronously for order', [
                    'order_id' => $order->id,
                    'shipment_code' => $shipment?->code,
                ]);

                return [
                    'status' => 'created',
                    'message' => 'ONESSTA shipment created.',
                    'shipment_code' => $shipment?->code,
                ];
            }

            $queuedShipment = OnesstaShipment::updateOrCreate(
                ['code' => $shipmentData['code']],
                [
                    'order_id' => $order->id,
                    'receiver' => $shipmentData['receiver'],
                    'phone' => $shipmentData['phone'],
                    'address' => $shipmentData['address'],
                    'city_id' => $shipmentData['city_id'] ?? null,
                    'price' => $shipmentData['price'],
                    'sku' => $shipmentData['sku'],
                    'note' => $shipmentData['note'],
                    'product_nature' => $shipmentData['product_nature'],
                    'is_cod' => $shipmentData['is_cod'],
                    'payment_situation' => $shipmentData['is_cod'] ? 'cod_awaiting' : 'pending',
                    'status' => 'QUEUED',
                    'raw_request' => $shipmentData,
                ]
            );

            CreateShipmentJob::dispatch($order->id, $shipmentData)
                ->onConnection($connection)
                ->onQueue($queue);

            Log::info('ONESSTA: CreateShipmentJob dispatched', [
                'order_id' => $order->id,
                'code' => $shipmentData['code'],
                'shipment_id' => $queuedShipment->id,
                'is_cod' => $shipmentData['is_cod'],
                'queue_connection' => $connection,
                'queue' => $queue,
            ]);

            return [
                'status' => 'queued',
                'message' => 'ONESSTA shipment creation queued.',
                'queue_connection' => $connection,
                'queue' => $queue,
            ];
        } catch (\Throwable $e) {
            Log::error('ONESSTA: Failed to dispatch shipment job', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function getShippingMethod($order): ?string
    {
        if (isset($order->shipping_method)) {
            return $order->shipping_method;
        }

        if (isset($order->shippingType) && $order->shippingType?->name) {
            return $order->shippingType->name;
        }

        return null;
    }

    private function buildShipmentData($order): array
    {
        $addressData = is_string($order->shipping_address)
            ? json_decode($order->shipping_address, true)
            : ($order->shipping_address ?? []);

        return [
            'code' => 'ORD-' . $order->code,
            'receiver' => $addressData['name'] ?? $order->user?->name ?? 'Unknown',
            'phone' => $addressData['phone'] ?? $order->user?->phone ?? '0000000000',
            'price' => (float) $order->grand_total,
            'city_id' => $addressData['city_id'] ?? $order->city_id ?? null,
            'city' => $addressData['city'] ?? 0,
            'address' => $addressData['address'] ?? 'No address provided',
            'sku' => $order->orderDetails?->pluck('sku')->filter()->join(';') ?: null,
            'note' => 'Order #' . $order->code,
            'product_nature' => 'general',
            'is_cod' => $this->isCOD($order),
        ];
    }

    private function isCOD($order): bool
    {
        return in_array($order->payment_type ?? '', ['cod', 'cash_on_delivery', 'cash_on_pickup']);
    }

    private function skip(string $status, string $message): array
    {
        return [
            'status' => $status,
            'message' => $message,
        ];
    }
}

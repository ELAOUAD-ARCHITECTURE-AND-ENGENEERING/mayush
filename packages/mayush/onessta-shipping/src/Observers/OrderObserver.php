<?php

namespace Mayush\Shipping\Onessta\Observers;

use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Jobs\CreateShipmentJob;

class OrderObserver
{
    public function created($order): void
    {

        if (!config('onessta.enabled', false)) {
            return;
        }

        if (!addon_is_activated('onessta')) {
            return;
        }

        $shippingMethod = $this->getShippingMethod($order);

        $allowedMethods = ['onessta', 'home_delivery'];
        if (!in_array($shippingMethod, $allowedMethods) && !in_array($order->shipping_type, $allowedMethods)) {
            return;
        }

        if ($order->delivery_status === 'cancelled') {
            Log::info('ONESSTA: Order cancelled, skipping shipment creation', [
                'order_id' => $order->id,
            ]);
            return;
        }

        $this->dispatchShipmentJob($order);
    }

    public function updated($order): void
    {

        $this->created($order);
    }

    private function getShippingMethod($order): ?string
    {
        if (isset($order->shipping_method)) {
            return $order->shipping_method;
        }

        if ($order->shippingType?->name) {
            return $order->shippingType->name;
        }

        return null;
    }

    private function dispatchShipmentJob($order): void
    {
        if ($this->shipmentExists($order->id)) {
            Log::info('ONESSTA: Shipment already exists for order, skipping', [
                'order_id' => $order->id,
            ]);
            return;
        }

        $addressData = is_string($order->shipping_address)
            ? json_decode($order->shipping_address, true)
            : ($order->shipping_address ?? []);

        $shipmentData = [
            'code' => 'ORD-' . $order->code,
            'receiver' => $addressData['name'] ?? $order->user?->name ?? 'Unknown',
            'phone' => $addressData['phone'] ?? $order->user?->phone ?? '0000000000',
            'price' => (float) $order->grand_total,
            'city_id' => $addressData['city_id'] ?? $order->city_id ?? null,
            'city' => $addressData['city'] ?? 0,
            'address' => $addressData['address'] ?? 'No address provided',
            'sku' => $order->orderDetails?->pluck('sku')?->join(';') ?? null,
            'note' => 'Order #' . $order->code,
            'product_nature' => 'general',
            'is_cod' => $this->isCOD($order),
        ];


        try {
            CreateShipmentJob::dispatch($order->id, $shipmentData);

            Log::info('ONESSTA: CreateShipmentJob dispatched', [
                'order_id' => $order->id,
                'code' => $shipmentData['code'],
                'is_cod' => $shipmentData['is_cod'],
            ]);
        } catch (\Exception $e) {
            Log::error('ONESSTA: Failed to dispatch shipment job', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function isCOD($order): bool
    {
        return in_array($order->payment_type ?? '', ['cod', 'cash_on_delivery', 'cash_on_pickup']);
    }

    private function shipmentExists(int $orderId): bool
    {
        return \Mayush\Shipping\Onessta\Models\OnesstaShipment::where('order_id', $orderId)->exists();
    }
}

<?php

namespace Mayush\Shipping\Onessta\Listeners;

use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Events\ShipmentStatusUpdated;
use Mayush\Shipping\Onessta\Services\TrackingService;

class UpdateOrderDeliveryStatus
{
    public function __construct(
        private readonly TrackingService $trackingService
    ) {}

    public function handle(ShipmentStatusUpdated $event): void
    {
        $shipment = $event->shipment;
        $order = $shipment->order;

        if (!$order) {
            Log::warning('ONESSTA: Shipment has no linked order', [
                'shipment_code' => $shipment->code,
            ]);
            return;
        }

        $deliveryStatus = $this->trackingService->toDeliveryStatus($event->newStatus);

        $order->update(['delivery_status' => $deliveryStatus]);

        Log::info('ONESSTA: Order delivery status updated', [
            'order_id' => $order->id,
            'shipment_code' => $shipment->code,
            'new_status' => $event->newStatus,
            'delivery_status' => $deliveryStatus,
        ]);
    }
}

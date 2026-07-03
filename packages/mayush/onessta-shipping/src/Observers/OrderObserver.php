<?php

namespace Mayush\Shipping\Onessta\Observers;

use Mayush\Shipping\Onessta\Services\OrderShipmentDispatchService;

class OrderObserver
{
    /**
     * Trigger shipment creation as soon as an eligible order is created.
     */
    public function created($order): void
    {
        app(OrderShipmentDispatchService::class)->ensureForOrder($order);
    }

    /**
     * Keep admin confirmation as a retry path. ensureForOrder is idempotent,
     * so this will not create duplicate ONESSTA shipments.
     */
    public function updated($order): void
    {
        if (!$order->wasChanged('is_confirmed') || !$order->is_confirmed) {
            return;
        }

        app(OrderShipmentDispatchService::class)->ensureForOrder($order);
    }
}

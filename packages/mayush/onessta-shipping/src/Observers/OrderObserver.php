<?php

namespace Mayush\Shipping\Onessta\Observers;

use Mayush\Shipping\Onessta\Services\OrderShipmentDispatchService;

class OrderObserver
{
    /**
     * Do NOT trigger shipment on creation.
     * Shipment is only dispatched after admin manually confirms the order (is_confirmed = true).
     */
    public function created($order): void
    {
        // Intentionally empty — shipment requires admin confirmation first.
        // See updated() below.
    }

    /**
     * Trigger shipment only when is_confirmed changes to true.
     * This happens when an admin toggles the confirmation checkbox after
     * verifying the order with the customer by phone.
     */
    public function updated($order): void
    {
        // Only proceed if is_confirmed just changed to true in this save
        if (!$order->wasChanged('is_confirmed') || !$order->is_confirmed) {
            return;
        }

        app(OrderShipmentDispatchService::class)->ensureForOrder($order);
    }
}

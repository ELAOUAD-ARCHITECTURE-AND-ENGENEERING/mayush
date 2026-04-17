<?php

namespace Mayush\Shipping\Onessta\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;

class ShipmentStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly OnesstaShipment $shipment,
        public readonly string $previousStatus,
        public readonly string $newStatus
    ) {}
}

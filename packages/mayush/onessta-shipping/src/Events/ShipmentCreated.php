<?php

namespace Mayush\Shipping\Onessta\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;

class ShipmentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly OnesstaShipment $shipment
    ) {}
}

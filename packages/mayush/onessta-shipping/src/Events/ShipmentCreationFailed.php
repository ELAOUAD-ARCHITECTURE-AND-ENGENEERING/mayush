<?php

namespace Mayush\Shipping\Onessta\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;

class ShipmentCreationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ?int $orderId,
        public readonly string $errorMessage
    ) {}
}

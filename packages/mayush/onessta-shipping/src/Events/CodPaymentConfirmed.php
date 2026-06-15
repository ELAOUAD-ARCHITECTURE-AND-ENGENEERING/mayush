<?php

namespace Mayush\Shipping\Onessta\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;

/**
 * CodPaymentConfirmed
 *
 * Fired when ONESSTA confirms that a Cash-on-Delivery payment has been
 * collected by the courier (parcel.status_updated with situation=PAID).
 *
 * The main application should register a listener for this event to:
 *  - Calculate seller commissions via calculateCommissionAffilationClubPoint()
 *  - Send the buyer a payment-received notification email
 *  - Trigger any loyalty / wallet credit logic
 *
 * This decouples the onessta-shipping package from the host application's
 * global helpers, keeping the package fully self-contained.
 */
class CodPaymentConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly OnesstaShipment $shipment,
        public readonly Order           $order,
    ) {}
}

<?php

namespace Mayush\Shipping\Onessta\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mayush\Shipping\Onessta\Events\ShipmentCreationFailed;

class NotifyAdminOnShipmentFailure
{
    public function handle(ShipmentCreationFailed $event): void
    {
        Log::error('ONESSTA: Shipment creation failed permanently', [
            'order_id' => $event->orderId,
            'error' => $event->errorMessage,
        ]);

        if ($event->orderId) {
            Notification::route('mail', config('onessta.admin_email', config('mail.from.address')))
                ->notify(new \Mayush\Shipping\Onessta\Notifications\ShipmentCreationFailedNotification(
                    $event->orderId,
                    $event->errorMessage
                ));
        }
    }
}

<?php

namespace Mayush\Shipping\Onessta\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipmentCreationFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $orderId,
        public readonly string $errorMessage
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[ONESSTA] Shipment Creation Failed — Order #' . $this->orderId)
            ->line('ONESSTA shipment creation has failed for the following order.')
            ->line("**Order ID:** #{$this->orderId}")
            ->line("**Error:** {$this->errorMessage}")
            ->line('Please check your ONESSTA configuration and ensure the cities are synced before retrying.')
            ->action('View Order', url('/admin/orders/' . $this->orderId))
            ->line('This is an automated notification from the ONESSTA Shipping Addon.');
    }
}

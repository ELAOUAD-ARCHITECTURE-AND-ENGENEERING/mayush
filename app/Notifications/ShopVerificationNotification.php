<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable as BusQueueable;
use Illuminate\Queue\SerializesModels;

class ShopVerificationNotification extends Notification implements ShouldQueue
{
    use BusQueueable, SerializesModels;

    public $data;
    public $className;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->onQueue('notifications');
        $this->afterCommit = true;

        $this->data = $data;
        $this->className = self::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [DbNotification::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $shop = $this->data['shop'] ?? null;
        $shopName = is_array($shop) ? ($shop['name'] ?? '') : ($shop?->name ?? '');
        $shopId = is_array($shop) ? ($shop['id'] ?? null) : ($shop?->id ?? null);
        $approvalStatus = is_array($shop)
            ? ($shop['approval_status'] ?? null)
            : ($shop?->approval_status ?? null);

        return [
            'notification_type_id' => $this->data['notification_type_id'] ?? null,
            'data' => [
                'name'  => $shopName,
                'id'    => $shopId,
                'status'=> $this->data['status'] ?? null,
                'workflow' => $this->data['workflow'] ?? null,
                'approval_status' => $this->data['approval_status'] ?? $approvalStatus,
                'document_id' => $this->data['document_id'] ?? null,
                'document_type' => $this->data['document_type'] ?? null,
                'document_version' => $this->data['document_version'] ?? null,
                'reason' => $this->data['reason'] ?? null,
                'target_route_name' => $this->data['target_route_name'] ?? null,
                'target_route_parameters' => $this->data['target_route_parameters'] ?? [],
            ]
        ];
    }
}

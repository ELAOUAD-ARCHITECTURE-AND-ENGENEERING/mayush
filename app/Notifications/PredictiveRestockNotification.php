<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PredictiveRestockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data;
    public $className;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->className = PredictiveRestockNotification::class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [DbNotification::class];
        
        // Add mail channel if user has email configured or if it's admin (as requested)
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $product_name = $this->data['product']['name'];
        $days = round($this->data['days_remaining'], 1);
        $stock = $this->data['current_stock'];

        return (new MailMessage)
                    ->subject('Critical Stock Alert: ' . $product_name)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('Our predictive analytics system has detected a potential stock-out for one of your products.')
                    ->line('**Product:** ' . $product_name)
                    ->line('**Current Stock:** ' . $stock)
                    ->line('**Predicted Stock-out in:** ' . $days . ' days')
                    ->action('Manage Stock', route('seller.products.index'))
                    ->line('We recommend replenishing your stock soon to avoid lost sales.')
                    ->line('Thank you for using Mayush!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'notification_type_id' => 106,
            'data' => [
                'id'             => $this->data['product']['id'],
                'name'           => $this->data['product']['name'],
                'days_remaining' => $this->data['days_remaining'],
                'current_stock'  => $this->data['current_stock'],
                'type'           => 'restock_alert'
            ]
        ];
    }
}

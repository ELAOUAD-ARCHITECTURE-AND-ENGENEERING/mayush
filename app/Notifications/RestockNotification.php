<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class RestockNotification extends Notification implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 60;

    use Queueable;

    protected $product;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->onQueue('notifications');
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
                    ->subject(translate('Good News! ') . $this->product->getTranslation('name') . translate(' is back in stock!'))
                    ->greeting(translate('Hello ') . $notifiable->name . '!')
                    ->line(translate('The product you were waiting for is now available in our store.'))
                    ->action(translate('Buy Now'), route('product', $this->product->slug))
                    ->line(translate('Thank you for choosing Mayush!'));
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
            'product_id' => $this->product->id,
            'product_name' => $this->product->getTranslation('name'),
        ];
    }
}

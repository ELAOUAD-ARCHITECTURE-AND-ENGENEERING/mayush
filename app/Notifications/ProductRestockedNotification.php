<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRestockedNotification extends Notification implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 60;

    use Queueable;

    public $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->onQueue('notifications');
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Back in Stock: ' . $this->product->getTranslation('name'))
                    ->view('emails.stock_alert', [
                        'product' => $this->product,
                        'user' => $notifiable
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'restock',
            'product_id' => $this->product->id,
            'message' => 'Your wishlisted item ' . $this->product->getTranslation('name') . ' is back in stock!',
            'link' => route('product', $this->product->slug),
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\EliteSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EliteApplicationNotification extends Notification
{
    use Queueable;

    protected $subscription;

    public function __construct(EliteSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $shop = $this->subscription->shop;
        $user = $shop ? $shop->user : null;

        return [
            'type'            => 'elite_application',
            'subscription_id' => $this->subscription->id,
            'shop_id'         => $this->subscription->shop_id,
            'shop_name'       => $shop ? $shop->name : 'Unknown',
            'seller_name'     => $user ? $user->name : 'Unknown',
            'seller_email'    => $user ? $user->email : '',
            'billing_cycle'   => $this->subscription->billing_cycle,
            'amount_paid'     => $this->subscription->amount_paid,
            'transaction_id'  => $this->subscription->transaction_id,
            'payment_method'  => $this->subscription->payment_method,
            'status'          => $this->subscription->status,
            'message'         => 'New Elite Artisan application from ' . ($shop ? $shop->name : 'Unknown'),
        ];
    }
}

<?php

namespace App\Listeners;

use App\Events\ProductRestockedEvent;
use App\Models\Wishlist;
use App\Notifications\ProductRestockedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendStockAlertNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductRestockedEvent $event): void
    {
        $product = $event->product;

        // 1. Notify Stock Subscribers (MA-107)
        $subscriptions = \App\Models\StockSubscription::pending()
            ->where('product_id', $product->id)
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->user_id) {
                // Registered User
                $subscription->user->notify(new ProductRestockedNotification($product));
            } else {
                // Guest Email
                Notification::route('mail', $subscription->email)
                    ->notify(new ProductRestockedNotification($product));
            }
            
            $subscription->update(['notified_at' => now()]);
        }

        // 2. Notify Wishlist Users (Existing logic)
        $wishlists = Wishlist::with('user')->where('product_id', $product->id)->get();
        $wishlistUsers = $wishlists->pluck('user')->filter();
        
        if ($wishlistUsers->isNotEmpty()) {
            Notification::send($wishlistUsers, new ProductRestockedNotification($product));
        }
    }
}

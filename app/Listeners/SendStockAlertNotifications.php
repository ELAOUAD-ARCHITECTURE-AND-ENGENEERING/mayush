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
        // Find all users who wishlisted this product
        $wishlists = Wishlist::with('user')->where('product_id', $event->product->id)->get();
        
        $users = $wishlists->pluck('user')->filter();
        
        if ($users->isNotEmpty()) {
            Notification::send($users, new ProductRestockedNotification($event->product));
        }
    }
}

<?php

namespace App\Observers;

use App\Models\ProductStock;
use App\Models\StockAlertSubscription;
use App\Notifications\RestockNotification;
use Notification;

class ProductStockObserver
{
    /**
     * Handle the ProductStock "updated" event.
     *
     * @param  \App\Models\ProductStock  $productStock
     * @return void
     */
    public function updated(ProductStock $productStock)
    {
        // Check if stock was 0 and is now greater than 0
        if ($productStock->qty > 0 && $productStock->getOriginal('qty') == 0) {
            $this->notifySubscribers($productStock);
        }
    }

    /**
     * Notify subscribers that the product is back in stock.
     */
    protected function notifySubscribers(ProductStock $productStock)
    {
        $subscriptions = StockAlertSubscription::where('product_id', $productStock->product_id)
            ->where('notified', 0)
            ->with('user')
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->user) {
                $subscription->user->notify(new RestockNotification($productStock->product_id ? $productStock->product : $subscription->product));
                
                // Mark as notified
                $subscription->update(['notified' => 1]);
            }
        }
    }
}

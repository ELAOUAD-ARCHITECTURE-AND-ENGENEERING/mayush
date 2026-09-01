<?php

namespace App\Observers;

use App\Models\ProductStock;
use App\Models\StockAlertSubscription;
use App\Notifications\RestockNotification;
use App\Services\Notifications\NotificationDispatcher;
use Notification;
use App\Services\InspirationCacheService;

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
        app(InspirationCacheService::class)->invalidateForProduct((int) $productStock->product_id);

        // Check if stock was 0 and is now greater than 0
        if ($productStock->qty > 0 && $productStock->getOriginal('qty') == 0) {
            $this->notifySubscribers($productStock);
        }
    }

    public function created(ProductStock $productStock): void
    {
        app(InspirationCacheService::class)->invalidateForProduct((int) $productStock->product_id);
    }

    public function deleted(ProductStock $productStock): void
    {
        app(InspirationCacheService::class)->invalidateForProduct((int) $productStock->product_id);
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
                if (config('notifications_v2.enabled')) {
                    app(NotificationDispatcher::class)->dispatch(
                        'product.restocked',
                        'stock_alert_subscription',
                        $subscription->id,
                        'restock:'.$subscription->id,
                        [$subscription->user_id],
                        [
                            'product_id' => $productStock->product_id,
                            'title' => 'Product back in stock',
                            'message' => 'A product you follow is back in stock.',
                            'action_url' => $productStock->product?->slug
                                ? route('product', $productStock->product->slug, false)
                                : null,
                        ]
                    );
                } else {
                    $subscription->user->notify(new RestockNotification($productStock->product_id ? $productStock->product : $subscription->product));
                }
                
                // Mark as notified
                $subscription->update(['notified' => 1]);
            }
        }
    }
}

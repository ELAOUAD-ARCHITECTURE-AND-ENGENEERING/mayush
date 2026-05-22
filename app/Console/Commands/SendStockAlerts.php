<?php

namespace App\Console\Commands;

use App\Mail\StockAlertMail;
use App\Models\StockSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:send-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send stock alerts for back-in-stock products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sent = 0;

        $subscriptions = StockSubscription::pending()
            ->with('product.stocks', 'user')
            ->get();

        foreach ($subscriptions as $subscription) {
            $product = $subscription->product;

            if (!$product || $this->availableQuantity($subscription) <= 0) {
                continue;
            }

            try {
                Mail::to($subscription->email)->queue(new StockAlertMail($product, $subscription->user));
                $subscription->forceFill(['notified_at' => now()])->save();
                $sent++;
            } catch (Throwable $exception) {
                Log::error('Failed to queue stock alert notification.', [
                    'subscription_id' => $subscription->id,
                    'product_id' => $subscription->product_id,
                    'email' => $subscription->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
        
        $this->info("Stock alerts processed successfully. {$sent} notification(s) queued.");

        return self::SUCCESS;
    }

    private function availableQuantity(StockSubscription $subscription): int
    {
        $product = $subscription->product;

        if (!$product) {
            return 0;
        }

        if ($subscription->variant) {
            return (int) optional(\App\Utility\CartUtility::find_product_stock($product, $subscription->variant))->qty;
        }

        if ($product->variant_product) {
            return (int) $product->stocks->sum('qty');
        }

        $stock = $product->stocks->first();

        return $stock ? (int) $stock->qty : (int) $product->current_stock;
    }
}

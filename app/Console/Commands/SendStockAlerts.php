<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $subscriptions = \App\Models\StockAlertSubscription::where('notified', false)->with('product.stocks', 'user')->get();

        foreach ($subscriptions as $subscription) {
            $product = $subscription->product;
            if (!$product) continue;

            $qty = 0;
            if ($product->variant_product) {
                foreach ($product->stocks as $stock) {
                    $qty += $stock->qty;
                }
            } else {
                $qty = optional($product->stocks->first())->qty;
            }

            if ($qty > 0) {
                if ($subscription->user) {
                    \Illuminate\Support\Facades\Mail::to($subscription->user->email)->queue(new \App\Mail\StockAlertMail($product, $subscription->user));
                }
                $subscription->notified = true;
                $subscription->save();
            }
        }
        
        $this->info('Stock alerts processed successfully.');
    }
}

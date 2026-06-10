<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentAttempt;
use App\Models\CombinedOrder;
use App\Services\PaymentStateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\InventoryLog;

class ExpireStalePayments extends Command
{
    protected $signature = 'mayush:payments:expire-stale';
    protected $description = 'Automatically expire stale payment attempts and cancel related unpaid orders to restore stock';

    public function handle(PaymentStateService $paymentStateService)
    {
        $this->info('Scanning for stale payment attempts...');
        
        // 60 minutes is safe, as CMI session usually expires in 15-30 mins
        $staleThreshold = Carbon::now()->subMinutes(60);
        
        $staleAttempts = PaymentAttempt::where('status', 'initiated')
            ->where('initiated_at', '<', $staleThreshold)
            ->get();
            
        if ($staleAttempts->isEmpty()) {
            $this->info('No stale payment attempts found.');
            return;
        }

        foreach ($staleAttempts as $attempt) {
            $this->line("Expiring Payment Attempt #{$attempt->id} (Order: {$attempt->merchant_reference})");
            
            $transitioned = $paymentStateService->transitionPaymentAttempt($attempt, 'expired', [
                'reason' => 'auto_expired_by_system'
            ]);

            if ($transitioned) {
                if ($attempt->combined_order_id) {
                    $combinedOrder = CombinedOrder::find($attempt->combined_order_id);
                    if ($combinedOrder) {
                        foreach ($combinedOrder->orders as $order) {
                            // Only cancel if it's still unpaid and not already cancelled
                            if ($order->payment_status === 'unpaid' && $order->delivery_status !== 'cancelled') {
                                $order->delivery_status = 'cancelled';
                                $order->save();
                                
                                foreach ($order->orderDetails as $detail) {
                                    $detail->delivery_status = 'cancelled';
                                    $detail->save();
                                    
                                    // Restore Stock
                                    $product = $detail->product;
                                    if ($product && $product->digital != 1) {
                                        $product_stock = $product->stocks()->where('variant', $detail->variation)->first();
                                        if (!$product_stock) {
                                            $product_stock = $product->stocks()->first();
                                        }

                                        if ($product_stock) {
                                            $previous_qty = $product_stock->qty;
                                            $product_stock->qty += $detail->quantity;
                                            $product_stock->save();
                                            
                                            InventoryLog::create([
                                                'product_id' => $product->id,
                                                'user_id' => $order->user_id,
                                                'quantity_delta' => $detail->quantity,
                                                'previous_stock' => $previous_qty,
                                                'current_stock' => $product_stock->qty,
                                                'reason' => 'auto_cancelled_stale_payment',
                                                'order_id' => $order->id
                                            ]);
                                        }
                                        
                                        $product->num_of_sale = max(0, $product->num_of_sale - $detail->quantity);
                                        $product->save();

                                        if ($product->added_by == 'seller' && $product->user && $product->user->seller) {
                                            $product->user->seller->num_of_sale = max(0, $product->user->seller->num_of_sale - $detail->quantity);
                                            $product->user->seller->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                Log::info("Auto-expired stale payment attempt {$attempt->id} and cancelled related unpaid orders to restore stock.");
            }
        }
        
        $this->info("Expired {$staleAttempts->count()} stale payments.");
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OrderDetail;
use App\Models\FrequentlyBoughtProduct;
use Illuminate\Support\Facades\DB;

class ProcessFrequentlyBoughtJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The minimum number of co-occurrences required to suggest an association.
     */
    protected $threshold = 2;

    public function __construct($threshold = 2)
    {
        $this->threshold = $threshold;
    }

    public function handle()
    {
        // 1. ANALYZE PURCHASE CO-OCCURRENCE (High Confidence)
        // Find pairs of products (A, B) that appear in the same 'delivered' or 'paid' order.
        $query = DB::table('order_details as or1')
            ->join('order_details as or2', function($join) {
                $join->on('or1.order_id', '=', 'or2.order_id')
                     ->on('or1.product_id', '<', 'or2.product_id');
            })
            ->join('orders', 'or1.order_id', '=', 'orders.id')
            ->where(function($query) {
                $query->where('orders.payment_status', 'paid')
                      ->orWhere('orders.delivery_status', 'delivered');
            })
            ->select('or1.product_id as p1', 'or2.product_id as p2', DB::raw('COUNT(*) as occurrences'))
            ->groupBy('p1', 'p2')
            ->having('occurrences', '>=', $this->threshold);

        $coPurchases = $query->get();

        foreach ($coPurchases as $pair) {
            $this->updateAssociation($pair->p1, $pair->p2);
            $this->updateAssociation($pair->p2, $pair->p1);
        }

        // 2. Fallback Logic: Associations within the same category for new products
        // (This ensures the UI isn't empty even for products with low purchase history)
        // ... can be added if needed, but for now we focus on data-driven truth.
    }

    protected function updateAssociation($productId, $relatedId)
    {
        // Ensure both products exist before associating
        if (DB::table('products')->where('id', $productId)->exists() && 
            DB::table('products')->where('id', $relatedId)->exists()) {
            
            DB::table('frequently_bought_products')->updateOrInsert(
                ['product_id' => $productId, 'frequently_bought_product_id' => $relatedId],
                ['category_id' => 0]
            );
        }
    }
}

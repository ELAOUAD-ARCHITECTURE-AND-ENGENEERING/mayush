<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use App\Models\Analytics\VendorPerformanceSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AggregateVendorPerformanceJob implements ShouldQueue
{
    public $tries = 1;
    public $timeout = 300;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?string $dateToAggregate;

    public function __construct(?string $dateToAggregate = null)
    {
        $this->onQueue('reports');
        $this->dateToAggregate = $dateToAggregate;
    }

    public function handle(): void
    {
        $targetDate = $this->dateToAggregate 
            ? Carbon::parse($this->dateToAggregate) 
            : Carbon::yesterday();

        $endOfDay = $targetDate->copy()->endOfDay();

        $shops = Shop::select('user_id', 'id')->get();
        $hasRatings = Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'rating');

        foreach ($shops as $shop) {
            $sellerId = $shop->user_id;

            // Total revenue (paid orders) up to target day
            $revenue = Order::where('seller_id', $sellerId)
                ->where('payment_status', 'paid')
                ->where('created_at', '<=', $endOfDay)
                ->sum('grand_total');

            // Order count up to target day (all paid orders or all orders? all paid)
            $ordersCount = Order::where('seller_id', $sellerId)
                ->where('payment_status', 'paid')
                ->where('created_at', '<=', $endOfDay)
                ->count();

            // Dispute count up to target day
            $disputesCount = RefundRequest::where('seller_id', $sellerId)
                ->where('created_at', '<=', $endOfDay)
                ->count();

            // Avg rating up to target day
            $avgRating = null;
            if ($hasRatings) {
                // average of their product reviews
                $avgRating = DB::table('reviews')
                    ->join('products', 'reviews.product_id', '=', 'products.id')
                    ->where('products.user_id', $sellerId)
                    ->where('reviews.created_at', '<=', $endOfDay)
                    ->avg('rating');
            }

            VendorPerformanceSnapshot::updateOrCreate(
                [
                    'seller_id' => $sellerId,
                    'snapshot_date' => $targetDate->toDateString(),
                ],
                [
                    'total_revenue' => $revenue ?: 0,
                    'orders_count' => $ordersCount ?: 0,
                    'dispute_count' => $disputesCount ?: 0,
                    'avg_rating' => $avgRating ? round($avgRating, 2) : null,
                ]
            );
        }
    }
}

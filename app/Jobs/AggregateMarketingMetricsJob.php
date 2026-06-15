<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\VisitorMetric;
use App\Models\Analytics\AnalyticsDailySummary;
use Carbon\Carbon;

class AggregateMarketingMetricsJob implements ShouldQueue
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

        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        // Coupon Revenue
        $couponRevenue = Order::where('coupon_discount', '>', 0)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('grand_total');
        $this->upsertSummary('coupon_revenue', 'global', $couponRevenue, $targetDate->toDateString());

        // Marketing Visits (With UTMs)
        $campaignVisits = VisitorMetric::whereNotNull('utm')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();
        $this->upsertSummary('campaign_visits', 'global', $campaignVisits, $targetDate->toDateString());

        // We can aggregate other data like active_coupons dynamically, wait!
        // Storing active coupons as a daily snapshot
        $activeCoupons = \App\Models\Coupon::where('status', 1)
            ->where('end_date', '>', $endOfDay->timestamp)
            ->where('start_date', '<=', $startOfDay->timestamp)
            ->count();
        $this->upsertSummary('active_coupons', 'global', $activeCoupons, $targetDate->toDateString());
    }

    private function upsertSummary(string $metric, string $dimension, float $value, string $date): void
    {
        AnalyticsDailySummary::updateOrCreate(
            ['metric_type' => $metric, 'dimension' => $dimension, 'date' => $date],
            ['value' => $value]
        );
    }
}

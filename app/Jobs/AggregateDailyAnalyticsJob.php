<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\CommissionHistory;
use App\Models\RefundRequest;
use App\Models\Analytics\AnalyticsDailySummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AggregateDailyAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?string $dateToAggregate;

    public function __construct(?string $dateToAggregate = null)
    {
        $this->dateToAggregate = $dateToAggregate;
    }

    public function handle(): void
    {
        $targetDate = $this->dateToAggregate 
            ? Carbon::parse($this->dateToAggregate) 
            : Carbon::yesterday();

        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        // 1. Gross GMV
        $grossGmv = Order::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('grand_total');
        $this->upsertSummary('gross_gmv', 'global', $grossGmv, $targetDate->toDateString());

        // 2. Net Revenue (Commission)
        $netRevenue = CommissionHistory::whereBetween('created_at', [$startOfDay, $endOfDay])->sum('admin_commission');
        $this->upsertSummary('net_revenue', 'global', $netRevenue, $targetDate->toDateString());

        // 3. Total Orders
        $totalOrders = Order::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
        $this->upsertSummary('total_orders', 'global', $totalOrders, $targetDate->toDateString());

        // 4. Total Refunds
        $totalRefunds = RefundRequest::where('refund_status', 1)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();
        $this->upsertSummary('total_refunds', 'global', $totalRefunds, $targetDate->toDateString());

        // 5. Refund Rate
        $refundRate = $totalOrders > 0 ? round(($totalRefunds / $totalOrders) * 100, 2) : 0;
        $this->upsertSummary('refund_rate', 'global', $refundRate, $targetDate->toDateString());
    }

    private function upsertSummary(string $metric, string $dimension, float $value, string $date): void
    {
        AnalyticsDailySummary::updateOrCreate(
            ['metric_type' => $metric, 'dimension' => $dimension, 'date' => $date],
            ['value' => $value]
        );
    }
}

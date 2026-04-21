<?php

namespace App\Repositories\Analytics;

use App\Contracts\Analytics\FinanceAnalyticsRepositoryInterface;
use App\DTOs\Analytics\RevenueMetricsDTO;
use App\Models\Analytics\AnalyticsDailySummary;
use App\Models\SellerWithdrawRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceAnalyticsRepository implements FinanceAnalyticsRepositoryInterface
{
    public function getRevenueMetrics(Carbon $start, Carbon $end): RevenueMetricsDTO
    {
        $diff = $start->diffInDays($end);
        $prevStart = (clone $start)->subDays($diff + 1);
        $prevEnd = (clone $start)->subDay();

        $grossGmv = AnalyticsDailySummary::where('metric_type', 'gross_gmv')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('value');
            
        $prevGrossGmv = AnalyticsDailySummary::where('metric_type', 'gross_gmv')
            ->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->sum('value');

        $netRevenue = AnalyticsDailySummary::where('metric_type', 'net_revenue')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('value');

        $prevNetRevenue = AnalyticsDailySummary::where('metric_type', 'net_revenue')
            ->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->sum('value');

        $totalOrders = AnalyticsDailySummary::where('metric_type', 'total_orders')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('value');

        $refunds = AnalyticsDailySummary::where('metric_type', 'total_refunds')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('value');

        $prevTotalOrders = AnalyticsDailySummary::where('metric_type', 'total_orders')
            ->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->sum('value');

        $prevRefunds = AnalyticsDailySummary::where('metric_type', 'total_refunds')
            ->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->sum('value');

        $refundRate = $totalOrders > 0 ? round(($refunds / $totalOrders) * 100, 2) : 0;
        $prevRefundRate = $prevTotalOrders > 0 ? round(($prevRefunds / $prevTotalOrders) * 100, 2) : 0;

        $pendingPayouts = SellerWithdrawRequest::where('status', 0)->sum('amount');
        $pendingVendors = SellerWithdrawRequest::where('status', 0)->distinct('user_id')->count();

        return new RevenueMetricsDTO([
            'gross_gmv' => (float)$grossGmv,
            'gross_gmv_delta' => $this->getDelta($grossGmv, $prevGrossGmv),
            'net_revenue' => (float)$netRevenue,
            'net_revenue_delta' => $this->getDelta($netRevenue, $prevNetRevenue),
            'commission' => (float)$netRevenue,
            'commission_delta' => $this->getDelta($netRevenue, $prevNetRevenue),
            'refund_rate' => (float)$refundRate,
            'refund_delta' => $this->getDelta($refundRate, $prevRefundRate, true),
            'pending_payouts' => (float)$pendingPayouts,
            'pending_vendors' => $pendingVendors,
        ]);
    }

    public function getRefundTrends(Carbon $start, Carbon $end): Collection
    {
        return AnalyticsDailySummary::where('metric_type', 'refund_rate')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date', 'asc')
            ->get(['date', 'value']);
    }

    public function getPayouts(int $limit = 6): Collection
    {
        return SellerWithdrawRequest::with(['user', 'shop'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($r) {
                return [
                    'vendor' => $r->shop ? $r->shop->name : ($r->user ? $r->user->name : 'Unknown'),
                    'amount' => (float)$r->amount,
                    'status' => $r->status == 1 ? 'Paid' : ($r->status == 2 ? 'Processing' : 'Pending'),
                    'date' => $r->created_at->format('d M Y')
                ];
            });
    }

    private function getDelta($current, $previous, $lowerIsBetter = false): string
    {
        if ($previous == 0) return $current > 0 ? '+100%' : '0%';
        $pct = round((($current - $previous) / $previous) * 100, 1);
        $sign = $pct > 0 ? '+' : '';
        return $sign . $pct . '%';
    }
}

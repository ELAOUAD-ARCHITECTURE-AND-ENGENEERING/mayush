<?php

namespace App\Services\Analytics;

use App\Contracts\Analytics\TechnicalAnalyticsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TechnicalAnalyticsService
{
    private TechnicalAnalyticsRepositoryInterface $repository;

    public function __construct(TechnicalAnalyticsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getDashboardMetrics(Carbon $start, Carbon $end): array
    {
        $cacheKey = "analytics_full_{$start->toDateString()}_{$end->toDateString()}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($start, $end) {
            return [
                // Finance
                'kpis' => $this->repository->getRevenueMetrics($start, $end),
                'refund_trend' => $this->repository->getRefundTrends($start, $end),
                'payouts' => $this->repository->getPayouts(),
                'revenue_trend' => $this->repository->getGrossGmvTrends($start, $end),
                'finance_chart' => $this->repository->getFinanceChart($start, $end),
                'tax_collection' => $this->repository->getTaxCollection($start, $end),
                'profitability' => $this->repository->getProfitabilityPulse($start, $end),

                // Visitors
                'visitor_stats' => $this->repository->getVisitorStats($start, $end),
                'traffic_composition' => $this->repository->getTrafficComposition($start, $end),
                'hourly_traffic' => $this->repository->getHourlyTraffic(),
                'funnel_stats' => $this->repository->getFunnelStats($start, $end),

                // Vendors
                'vendor_directory' => $this->repository->getTopVendorsSnapshot(Carbon::now()),
                'vendor_kpis' => $this->repository->getVendorKpis($start, $end),
                'vendor_growth' => $this->repository->getVendorGrowthChart(),
                'category_distribution' => $this->repository->getCategoryDistribution($start, $end),

                // Marketing
                'marketing_metrics' => $this->repository->getMarketingMetrics($start, $end),
                'marketing_kpis' => $this->repository->getMarketingKpis($start, $end),
                'coupon_tracker' => $this->repository->getCouponTracker(),

                // Security
                'security_metrics' => $this->repository->getSecurityMetrics($start, $end),

                // Operations
                'system_health' => $this->repository->getSystemHealth(),
                'insights' => $this->repository->getAutomatedInsights(),
                'forecasting' => $this->repository->getForecastingData($start, $end),
                'currency' => $this->repository->getCurrencyConfig(),
            ];
        });
    }
}

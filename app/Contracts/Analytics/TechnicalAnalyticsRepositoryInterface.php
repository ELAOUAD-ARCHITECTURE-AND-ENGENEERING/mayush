<?php

namespace App\Contracts\Analytics;

use Carbon\Carbon;
use App\DTOs\Analytics\RevenueMetricsDTO;
use Illuminate\Support\Collection;

interface TechnicalAnalyticsRepositoryInterface
{
    // ── FINANCE ──
    public function getRevenueMetrics(Carbon $start, Carbon $end): RevenueMetricsDTO;
    public function getRefundTrends(Carbon $start, Carbon $end): Collection;
    public function getPayouts(int $limit = 6): Collection;
    public function getGrossGmvTrends(Carbon $start, Carbon $end): Collection;
    public function getFinanceChart(Carbon $start, Carbon $end): array;
    public function getTaxCollection(Carbon $start, Carbon $end): array;
    public function getProfitabilityPulse(Carbon $start, Carbon $end): array;

    // ── VISITORS & TRAFFIC ──
    public function getVisitorStats(Carbon $start, Carbon $end): array;
    public function getTrafficComposition(Carbon $start, Carbon $end): Collection;
    public function getHourlyTraffic(): array;
    public function getFunnelStats(Carbon $start, Carbon $end): array;

    // ── VENDORS ──
    public function getTopVendorsSnapshot(Carbon $date): Collection;
    public function getVendorKpis(Carbon $start, Carbon $end): array;
    public function getVendorGrowthChart(): array;
    public function getCategoryDistribution(Carbon $start, Carbon $end): array;

    // ── MARKETING ──
    public function getMarketingMetrics(Carbon $start, Carbon $end): array;
    public function getMarketingKpis(Carbon $start, Carbon $end): array;
    public function getCouponTracker(): array;

    // ── SECURITY ──
    public function getSecurityMetrics(Carbon $start, Carbon $end): array;

    // ── OPERATIONS ──
    public function getSystemHealth(): array;
    public function getAutomatedInsights(): array;
    public function getForecastingData(Carbon $start, Carbon $end): array;
    public function getCurrencyConfig(): array;
}

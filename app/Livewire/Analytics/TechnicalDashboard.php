<?php

namespace App\Livewire\Analytics;

use Livewire\Component;
use Carbon\Carbon;
use App\Services\Analytics\TechnicalAnalyticsService;
use Illuminate\Support\Facades\Log;

class TechnicalDashboard extends Component
{
    public string $dateRange = '30D';
    public string $activeTab = 'Overview';

    // Finance
    public array $financeKpis = [];
    public array $refundTrend = [];
    public array $payouts = [];
    public array $revenueTrend = [];
    public array $financeChart = [];
    public array $taxCollection = [];
    public array $profitability = [];

    // Visitors
    public array $visitorStats = [];
    public array $trafficComposition = [];
    public array $hourlyTraffic = [];
    public array $funnelStats = [];

    // Vendors
    public array $vendorDirectory = [];
    public array $vendorKpis = [];
    public array $vendorGrowth = [];
    public array $categoryDistribution = [];

    // Marketing
    public array $marketingMetrics = [];
    public array $marketingKpis = [];
    public array $couponTracker = [];

    // Security
    public array $securityMetrics = [];

    // Operations
    public array $systemHealth = [];
    public array $insights = [];
    public array $forecasting = [];
    public array $currency = [];

    public function mount(TechnicalAnalyticsService $analyticsService)
    {
        $this->loadData($analyticsService);
    }

    public function setDateRange($range, TechnicalAnalyticsService $analyticsService)
    {
        $this->dateRange = $range;
        $this->loadData($analyticsService);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    private function loadData(TechnicalAnalyticsService $analyticsService)
    {
        try {
            $end = Carbon::now();
            $start = match($this->dateRange) {
                'Today' => Carbon::now()->startOfDay(),
                '7D' => Carbon::now()->subDays(7),
                '90D' => Carbon::now()->subDays(90),
                default => Carbon::now()->subDays(30),
            };

            $data = $analyticsService->getDashboardMetrics($start, $end);

            // Finance KPIs
            $kpis = $data['kpis'];
            $this->financeKpis = method_exists($kpis, 'toArray') ? $kpis->toArray() : (array) $kpis;

            // Safe conversions
            $this->refundTrend = $this->safe($data['refund_trend'] ?? []);
            $this->payouts = $this->safe($data['payouts'] ?? []);
            $this->revenueTrend = $this->safe($data['revenue_trend'] ?? []);
            $this->financeChart = $data['finance_chart'] ?? [];
            $this->taxCollection = $data['tax_collection'] ?? [];
            $this->profitability = $data['profitability'] ?? [];

            // Visitors
            $this->visitorStats = $data['visitor_stats'] ?? [];
            $this->trafficComposition = $this->safe($data['traffic_composition'] ?? []);
            $this->hourlyTraffic = $data['hourly_traffic'] ?? [];
            $this->funnelStats = $data['funnel_stats'] ?? [];

            // Vendors
            $vd = $data['vendor_directory'] ?? collect();
            $this->vendorDirectory = $vd instanceof \Illuminate\Support\Collection
                ? $vd->map(fn($v) => is_object($v) ? json_decode(json_encode($v), true) : (array)$v)->toArray()
                : (array) $vd;
            $this->vendorKpis = $data['vendor_kpis'] ?? [];
            $this->vendorGrowth = $data['vendor_growth'] ?? [];
            $this->categoryDistribution = $data['category_distribution'] ?? [];

            // Marketing
            $this->marketingMetrics = $data['marketing_metrics'] ?? [];
            $this->marketingKpis = $data['marketing_kpis'] ?? [];
            $this->couponTracker = $data['coupon_tracker'] ?? [];

            // Security
            $this->securityMetrics = $data['security_metrics'] ?? [];

            // Operations
            $this->systemHealth = $data['system_health'] ?? [];
            $this->insights = $data['insights'] ?? [];
            $this->forecasting = $data['forecasting'] ?? [];
            $this->currency = $data['currency'] ?? ['symbol' => '$', 'code' => 'USD'];

        } catch (\Throwable $e) {
            Log::error('[TechnicalDashboard] Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    private function safe($value): array
    {
        if ($value instanceof \Illuminate\Support\Collection) return $value->toArray();
        if (is_array($value)) return $value;
        if (is_object($value) && method_exists($value, 'toArray')) return $value->toArray();
        return (array) $value;
    }

    public function render()
    {
        return view('livewire.analytics.technical-dashboard');
    }
}

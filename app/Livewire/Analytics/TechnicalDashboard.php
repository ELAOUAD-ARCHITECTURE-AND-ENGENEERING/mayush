<?php

namespace App\Livewire\Analytics;

use Livewire\Component;
use Carbon\Carbon;
use App\Services\Analytics\FinanceAnalyticsService;

class TechnicalDashboard extends Component
{
    public string $dateRange = '30D';
    public string $activeTab = 'Overview';
    
    public array $financeKpis = [];
    public array $refundTrend = [];
    public array $payouts = [];

    public function mount(FinanceAnalyticsService $financeService)
    {
        $this->loadData($financeService);
    }

    public function setDateRange($range, FinanceAnalyticsService $financeService)
    {
        $this->dateRange = $range;
        $this->loadData($financeService);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    private function loadData(FinanceAnalyticsService $financeService)
    {
        $end = Carbon::now();
        $start = match($this->dateRange) {
            'Today' => Carbon::now()->startOfDay(),
            '7D' => Carbon::now()->subDays(7),
            '90D' => Carbon::now()->subDays(90),
            default => Carbon::now()->subDays(30),
        };

        $financeData = $financeService->getDashboardMetrics($start, $end);
        
        $this->financeKpis = (array) $financeData['kpis'];
        $this->refundTrend = $financeData['refund_trend']->toArray();
        $this->payouts = $financeData['payouts']->toArray();
    }

    public function render()
    {
        return view('livewire.analytics.technical-dashboard')
            ->layout('backend.layouts.app');
    }
}

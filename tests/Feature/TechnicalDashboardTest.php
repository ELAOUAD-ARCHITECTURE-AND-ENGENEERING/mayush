<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Analytics\TechnicalDashboard;
use App\Services\Analytics\TechnicalAnalyticsService;
use App\Contracts\Analytics\TechnicalAnalyticsRepositoryInterface;
use App\DTOs\Analytics\RevenueMetricsDTO;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Illuminate\Support\Collection;

class TechnicalDashboardTest extends TestCase
{
    public function test_it_renders_technical_dashboard_successfully()
    {
        $this->mockService();

        Livewire::test(TechnicalDashboard::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.analytics.technical-dashboard')
            ->assertSee('MarketOps Dashboard', false)
            ->assertSee('Revenue Analytics & Forecast', false)
            ->assertSee('System Health');
    }

    public function test_it_updates_metrics_when_date_range_changes()
    {
        $this->mockService();

        Livewire::test(TechnicalDashboard::class)
            ->call('setDateRange', '7D')
            ->assertSet('dateRange', '7D')
            ->assertStatus(200);
    }

    private function mockService()
    {
        Cache::flush();

        $mockRepo = Mockery::mock(TechnicalAnalyticsRepositoryInterface::class);
        $mockRepo->shouldReceive('getRevenueMetrics')->andReturn(new RevenueMetricsDTO([
            'gross_gmv' => 5000.00,
            'gross_gmv_delta' => '+5%',
            'net_revenue' => 500.00,
            'net_revenue_delta' => '+5%',
            'commission' => 500.00,
            'commission_delta' => '+5%',
            'refund_rate' => 1.5,
            'refund_delta' => '-0.5%',
            'pending_payouts' => 100.00,
            'pending_vendors' => 1,
        ]));
        $mockRepo->shouldReceive('getRefundTrends')->andReturn(new Collection([]));
        $mockRepo->shouldReceive('getPayouts')->andReturn(new Collection([]));
        $mockRepo->shouldReceive('getGrossGmvTrends')->andReturn(new Collection([]));
        $mockRepo->shouldReceive('getFinanceChart')->andReturn([]);
        $mockRepo->shouldReceive('getTaxCollection')->andReturn([]);
        $mockRepo->shouldReceive('getProfitabilityPulse')->andReturn([]);
        $mockRepo->shouldReceive('getVisitorStats')->andReturn([]);
        $mockRepo->shouldReceive('getTrafficComposition')->andReturn(new Collection([]));
        $mockRepo->shouldReceive('getHourlyTraffic')->andReturn([]);
        $mockRepo->shouldReceive('getFunnelStats')->andReturn([]);
        $mockRepo->shouldReceive('getTopVendorsSnapshot')->andReturn(new Collection([]));
        $mockRepo->shouldReceive('getVendorKpis')->andReturn([]);
        $mockRepo->shouldReceive('getVendorGrowthChart')->andReturn([]);
        $mockRepo->shouldReceive('getCategoryDistribution')->andReturn([]);
        $mockRepo->shouldReceive('getMarketingMetrics')->andReturn([]);
        $mockRepo->shouldReceive('getMarketingKpis')->andReturn([]);
        $mockRepo->shouldReceive('getCouponTracker')->andReturn([]);
        $mockRepo->shouldReceive('getSecurityMetrics')->andReturn([]);
        $mockRepo->shouldReceive('getSystemHealth')->andReturn([]);
        $mockRepo->shouldReceive('getAutomatedInsights')->andReturn([]);
        $mockRepo->shouldReceive('getForecastingData')->andReturn([]);
        $mockRepo->shouldReceive('getCurrencyConfig')->andReturn(['symbol' => '$', 'code' => 'USD']);

        $this->app->instance(TechnicalAnalyticsService::class, new TechnicalAnalyticsService($mockRepo));
    }
}

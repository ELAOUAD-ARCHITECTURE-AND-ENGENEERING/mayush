<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Analytics\TechnicalDashboard;
use App\Services\Analytics\FinanceAnalyticsService;
use App\Contracts\Analytics\FinanceAnalyticsRepositoryInterface;
use App\DTOs\Analytics\RevenueMetricsDTO;
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
            ->assertSee('Operations Dashboard', false)
            ->assertSee('Gross GMV')
            ->assertSee('Net Revenue');
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
        $mockRepo = Mockery::mock(FinanceAnalyticsRepositoryInterface::class);
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

        $this->app->instance(FinanceAnalyticsService::class, new FinanceAnalyticsService($mockRepo));
    }
}

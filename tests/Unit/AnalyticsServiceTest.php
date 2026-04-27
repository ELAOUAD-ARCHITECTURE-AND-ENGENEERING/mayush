<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Analytics\TechnicalAnalyticsService;
use App\Contracts\Analytics\TechnicalAnalyticsRepositoryInterface;
use App\DTOs\Analytics\RevenueMetricsDTO;
use Carbon\Carbon;
use Mockery;
use Illuminate\Support\Collection;

class AnalyticsServiceTest extends TestCase
{
    public function test_get_dashboard_metrics_returns_expected_structure(): void
    {
        $mockRepo = Mockery::mock(TechnicalAnalyticsRepositoryInterface::class);

        $mockDto = new RevenueMetricsDTO([
            'gross_gmv' => 1000.00,
            'gross_gmv_delta' => '+10%',
            'net_revenue' => 100.00,
            'net_revenue_delta' => '+5%',
            'commission' => 100.00,
            'commission_delta' => '+5%',
            'refund_rate' => 2.5,
            'refund_delta' => '-1%',
            'pending_payouts' => 50.00,
            'pending_vendors' => 2,
        ]);

        $start = Carbon::now()->subDays(30);
        $end = Carbon::now();

        $mockRepo->shouldReceive('getRevenueMetrics')
            ->withArgs(function ($s, $e) use ($start, $end) {
                return $s->format('Y-m-d') === $start->format('Y-m-d') &&
                       $e->format('Y-m-d') === $end->format('Y-m-d');
            })
            ->once()
            ->andReturn($mockDto);

        $mockRepo->shouldReceive('getRefundTrends')
            ->once()
            ->andReturn(new Collection([['date' => '2026-04-01', 'value' => 2.5]]));

        $mockRepo->shouldReceive('getPayouts')
            ->once()
            ->andReturn(new Collection([['vendor' => 'Test Vendor', 'amount' => 50.0, 'status' => 'Pending', 'date' => '2026-04-01']]));

        foreach ([
            'getFinanceChart',
            'getTaxCollection',
            'getProfitabilityPulse',
            'getVisitorStats',
            'getHourlyTraffic',
            'getFunnelStats',
            'getVendorKpis',
            'getVendorGrowthChart',
            'getCategoryDistribution',
            'getMarketingMetrics',
            'getMarketingKpis',
            'getCouponTracker',
            'getSecurityMetrics',
            'getSystemHealth',
            'getAutomatedInsights',
            'getForecastingData',
            'getCurrencyConfig',
        ] as $method) {
            $mockRepo->shouldReceive($method)->once()->andReturn([]);
        }

        foreach ([
            'getGrossGmvTrends',
            'getTrafficComposition',
            'getTopVendorsSnapshot',
        ] as $method) {
            $mockRepo->shouldReceive($method)->once()->andReturn(new Collection());
        }

        $service = new TechnicalAnalyticsService($mockRepo);
        $result = $service->getDashboardMetrics($start, $end);

        $this->assertArrayHasKey('kpis', $result);
        $this->assertArrayHasKey('refund_trend', $result);
        $this->assertArrayHasKey('payouts', $result);
        $this->assertArrayHasKey('revenue_trend', $result);
        $this->assertArrayHasKey('system_health', $result);

        $this->assertInstanceOf(RevenueMetricsDTO::class, $result['kpis']);
        $this->assertEquals(1000.00, $result['kpis']->grossGmv);
        $this->assertCount(1, $result['refund_trend']);
        $this->assertCount(1, $result['payouts']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

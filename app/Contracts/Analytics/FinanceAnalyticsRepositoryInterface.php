<?php

namespace App\Contracts\Analytics;

use Carbon\Carbon;
use App\DTOs\Analytics\RevenueMetricsDTO;
use Illuminate\Support\Collection;

interface FinanceAnalyticsRepositoryInterface
{
    public function getRevenueMetrics(Carbon $start, Carbon $end): RevenueMetricsDTO;
    public function getRefundTrends(Carbon $start, Carbon $end): Collection;
    public function getPayouts(int $limit = 6): Collection;
}

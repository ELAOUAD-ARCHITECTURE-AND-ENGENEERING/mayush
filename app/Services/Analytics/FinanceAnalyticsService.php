<?php

namespace App\Services\Analytics;

use App\Contracts\Analytics\FinanceAnalyticsRepositoryInterface;
use Carbon\Carbon;

class FinanceAnalyticsService
{
    private FinanceAnalyticsRepositoryInterface $repository;

    public function __construct(FinanceAnalyticsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getDashboardMetrics(Carbon $start, Carbon $end): array
    {
        $metrics = $this->repository->getRevenueMetrics($start, $end);
        $refundTrends = $this->repository->getRefundTrends($start, $end);
        $payouts = $this->repository->getPayouts();

        return [
            'kpis' => $metrics,
            'refund_trend' => $refundTrends,
            'payouts' => $payouts,
        ];
    }
}

<?php

namespace App\DTOs\Analytics;

class RevenueMetricsDTO
{
    public float $grossGmv;
    public string $grossGmvDelta;
    public float $netRevenue;
    public string $netRevenueDelta;
    public float $commission;
    public string $commissionDelta;
    public float $refundRate;
    public string $refundDelta;
    public float $pendingPayouts;
    public int $pendingVendors;

    public function __construct(array $data)
    {
        $this->grossGmv = $data['gross_gmv'] ?? 0.0;
        $this->grossGmvDelta = $data['gross_gmv_delta'] ?? '0%';
        $this->netRevenue = $data['net_revenue'] ?? 0.0;
        $this->netRevenueDelta = $data['net_revenue_delta'] ?? '0%';
        $this->commission = $data['commission'] ?? 0.0;
        $this->commissionDelta = $data['commission_delta'] ?? '0%';
        $this->refundRate = $data['refund_rate'] ?? 0.0;
        $this->refundDelta = $data['refund_delta'] ?? '0%';
        $this->pendingPayouts = $data['pending_payouts'] ?? 0.0;
        $this->pendingVendors = $data['pending_vendors'] ?? 0;
    }

    /**
     * Convert to array with snake_case keys matching the Blade template expectations.
     */
    public function toArray(): array
    {
        return [
            'gross_gmv' => $this->grossGmv,
            'gross_gmv_delta' => $this->grossGmvDelta,
            'net_revenue' => $this->netRevenue,
            'net_revenue_delta' => $this->netRevenueDelta,
            'commission' => $this->commission,
            'commission_delta' => $this->commissionDelta,
            'refund_rate' => $this->refundRate,
            'refund_delta' => $this->refundDelta,
            'pending_payouts' => $this->pendingPayouts,
            'pending_vendors' => $this->pendingVendors,
        ];
    }
}

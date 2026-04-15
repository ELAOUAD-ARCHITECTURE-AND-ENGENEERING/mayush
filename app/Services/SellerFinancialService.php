<?php

namespace App\Services;

use App\Models\Order;
use App\Models\CommissionHistory;
use App\Models\RefundRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SellerFinancialService
{
    /**
     * Get earnings summary for a seller within a date range.
     */
    public function getEarningsSummary($sellerId, Carbon $startDate, Carbon $endDate)
    {
        $stats = DB::table('commission_histories')
            ->where('seller_id', $sellerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('SUM(seller_earning) as total_net_earnings'),
                DB::raw('SUM(admin_commission) as total_commissions'),
                DB::raw('COUNT(DISTINCT order_id) as total_orders')
            ])
            ->first();

        $grossSales = ($stats->total_net_earnings ?? 0) + ($stats->total_commissions ?? 0);

        // Refund deductions (calculated as amount to be subtracted from next payout)
        $refunds = RefundRequest::where('seller_id', $sellerId)
            ->where('refund_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('refund_amount');

        return [
            'gross_sales' => round($grossSales, 2),
            'net_earnings' => round($stats->total_net_earnings ?? 0, 2),
            'commissions' => round($stats->total_commissions ?? 0, 2),
            'refunded' => round($refunds, 2),
            'order_count' => $stats->total_orders ?? 0,
            'payout_ready' => round(($stats->total_net_earnings ?? 0) - $refunds, 2),
        ];
    }

    /**
     * Get geo-analytics for a seller's orders.
     */
    public function getGeoStats($sellerId, Carbon $startDate, Carbon $endDate)
    {
        $orders = Order::where('seller_id', $sellerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $regions = [];
        foreach ($orders as $order) {
            $address = json_decode($order->shipping_address, true);
            $city = $address['city'] ?? 'Unknown';
            
            if (!isset($regions[$city])) {
                $regions[$city] = [
                    'city' => $city,
                    'order_count' => 0,
                    'revenue' => 0
                ];
            }
            
            $regions[$city]['order_count']++;
            $regions[$city]['revenue'] += $order->grand_total;
        }

        return array_values(collect($regions)->sortByDesc('revenue')->take(10)->toArray());
    }

    /**
     * Get projected earnings (unpaid/processing orders).
     */
    public function getProjectedEarnings($sellerId)
    {
        $pendingRevenue = Order::where('seller_id', $sellerId)
            ->where('payment_status', 'unpaid')
            ->whereNotIn('delivery_status', ['cancelled', 'returned'])
            ->sum('grand_total');

        return [
            'projected_gross' => round($pendingRevenue, 2),
            // Assuming average commission rate of 10% for projection if not yet in commission_histories
            'projected_net' => round($pendingRevenue * 0.9, 2) 
        ];
    }
}

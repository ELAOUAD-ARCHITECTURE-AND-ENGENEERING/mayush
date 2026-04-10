<?php

namespace App\Services;

use App\Models\VisitorMetric;
use App\Models\Order;
use App\Models\Shop;
use App\Models\RefundRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get detailed visitor stats including trends.
     */
    public function getVisitorStats(Carbon $startDate, Carbon $endDate)
    {
        // Try to get from summary table first
        $summary = DB::table('analytics_summaries')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->exists();

        if ($summary) {
            return $this->getHistoricalSummary($startDate, $endDate);
        }

        // Fallback to real-time calculation
        $stats = DB::table('visitor_metrics')
            ->select([
                DB::raw('COUNT(*) as total_visits'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors'),
                DB::raw('AVG(CASE WHEN time_spent > 0 THEN time_spent ELSE NULL END) as avg_duration'),
                DB::raw('COUNT(CASE WHEN is_entry = 1 AND is_exit = 1 THEN 1 ELSE NULL END) as total_bounces')
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();

        $bounceRate = $stats->total_visits > 0 
            ? round(($stats->total_bounces / $stats->total_visits) * 100, 1) 
            : 0;

        return [
            'total_visits' => $stats->total_visits,
            'unique_visitors' => $stats->unique_visitors,
            'bounce_rate' => $bounceRate,
            'avg_duration_sec' => round($stats->avg_duration ?? 0),
            'visit_trend' => $this->getTrendData($startDate, $endDate, 'count(*)'),
            'bounce_trend' => $this->getTrendData($startDate, $endDate, 'ROUND(SUM(CASE WHEN is_entry = 1 AND is_exit = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 1)'),
            'duration_trend' => $this->getTrendData($startDate, $endDate, 'ROUND(AVG(NULLIF(time_spent, 0)), 1)')
        ];
    }

    /**
     * Internal helper to calculate core metrics for aggregation.
     */
    public function getSummary($startDate, $endDate)
    {
        $stats = DB::table('visitor_metrics')
            ->select([
                DB::raw('COUNT(*) as total_visits'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors'),
                DB::raw('SUM(CASE WHEN is_entry = 1 AND is_exit = 1 THEN 1 ELSE 0 END) as bounces'),
                DB::raw('AVG(CASE WHEN time_spent > 0 THEN time_spent ELSE NULL END) as avg_duration')
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();

        return [
            'total_visits' => $stats->total_visits,
            'unique_visitors' => $stats->unique_visitors,
            'bounce_rate' => $stats->total_visits > 0 ? round(($stats->bounces / $stats->total_visits) * 100, 2) : 0,
            'avg_duration_sec' => round($stats->avg_duration ?? 0),
        ];
    }

    /**
     * Get historical summary from the aggregation table.
     */
    public function getHistoricalSummary($startDate, $endDate)
    {
        $sum = DB::table('analytics_summaries')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select([
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('SUM(visits) as total_visits'),
                DB::raw('SUM(unique_visitors) as unique_visitors'),
                DB::raw('AVG(bounce_rate) as avg_bounce_rate'),
                DB::raw('AVG(avg_duration_sec) as avg_duration_sec'),
                DB::raw('AVG(aov) as avg_aov'),
                DB::raw('SUM(orders) as total_orders')
            ])->first();

        $trends = DB::table('analytics_summaries')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get();

        return [
            'total_visits' => (int)$sum->total_visits,
            'unique_visitors' => (int)$sum->unique_visitors,
            'bounce_rate' => round($sum->avg_bounce_rate ?? 0, 1),
            'revenue' => (float)$sum->total_revenue,
            'avg_duration_sec' => round($sum->avg_duration_sec ?? 0),
            'visit_trend' => $trends->pluck('visits')->toArray(),
            'bounce_trend' => $trends->pluck('bounce_rate')->map(fn($v) => (float)$v)->toArray(),
            'duration_trend' => $trends->pluck('avg_duration_sec')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    /**
     * Get funnel conversion stats.
     */
    public function getFunnelStats($startDate, $endDate)
    {
        $metrics = DB::table('visitor_metrics')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('COUNT(*) as visits'),
                DB::raw('COUNT(CASE WHEN url LIKE "%/product/%" THEN 1 END) as product_views'),
                DB::raw('COUNT(CASE WHEN url LIKE "%/checkout%" THEN 1 END) as checkout')
            ])->first();

        $cartCount = DB::table('carts')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $purchaseCount = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->count();

        return [
            'visits' => $metrics->visits,
            'product_views' => $metrics->product_views,
            'add_to_cart' => $cartCount,
            'checkout' => $metrics->checkout,
            'purchased' => $purchaseCount,
        ];
    }

    private function getTrendData($startDate, $endDate, $expression)
    {
        return DB::table('visitor_metrics')
            ->select(DB::raw("DATE(created_at) as date, $expression as value"))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('value')
            ->toArray();
    }

    /**
     * Get behavior flow (Optimized SQL version).
     */
    public function getBehaviorFlow($startDate, $endDate) 
    {
        // Using a join to find transitions within sessions
        $transitions = DB::table('visitor_metrics as vm1')
            ->join('visitor_metrics as vm2', function($join) {
                $join->on('vm1.session_id', '=', 'vm2.session_id')
                     ->whereRaw('vm2.created_at > vm1.created_at');
            })
            ->select('vm1.url as source', 'vm2.url as target', DB::raw('COUNT(*) as value'))
            ->whereBetween('vm1.created_at', [$startDate, $endDate])
            ->whereBetween('vm2.created_at', [$startDate, $endDate])
            ->whereRaw('vm1.url != vm2.url') // Only transitions between different pages
            ->groupBy('source', 'target')
            ->orderByDesc('value')
            ->limit(20)
            ->get();

        return $transitions;
    }

    /**
     * Get heatmap data for a specific URL, aggregated into a grid.
     */
    public function getHeatmapGrid($url, $startDate, $endDate, $gridSize = 20)
    {
        $cacheKey = 'heatmap_' . md5($url . $startDate->toDateString() . $endDate->toDateString() . $gridSize);
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($url, $startDate, $endDate, $gridSize) {
            $metrics = DB::table('visitor_metrics')
                ->where('url', $url)
                ->whereNotNull('click_paths')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $points = [];
            foreach ($metrics as $metric) {
                $paths = is_string($metric->click_paths) ? json_decode($metric->click_paths, true) : $metric->click_paths;
                if (is_array($paths)) {
                    foreach ($paths as $p) {
                        if (isset($p['x']) && isset($p['y'])) {
                            $gridX = round($p['x'] / $gridSize) * $gridSize;
                            $gridY = round($p['y'] / $gridSize) * $gridSize;
                            $key = "$gridX,$gridY";
                            $points[$key] = ($points[$key] ?? 0) + 1;
                        }
                    }
                }
            }

            $heatmap = [];
            foreach ($points as $coords => $intensity) {
                [$x, $y] = explode(',', $coords);
                $heatmap[] = ['x' => (int)$x, 'y' => (int)$y, 'intensity' => $intensity];
            }

            return $heatmap;
        });
    }

    /**
     * Get heatmap data for a specific URL.
     */
    public function getHeatmapPoints($url, $startDate, $endDate)
    {
        return DB::table('visitor_metrics')
            ->where('url', $url)
            ->whereNotNull('click_paths')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('click_paths')
            ->flatMap(function($path) {
                return is_string($path) ? json_decode($path, true) : $path;
            })
            ->filter()
            ->values();
    }

    /**
     * Aggregate data into the summary table.
     */
    public function aggregateSummary($date)
    {
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        $visitorStats = $this->getSummary($dayStart, $dayEnd);
        
        $revenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->sum('grand_total');

        $orders = Order::whereBetween('created_at', [$dayStart, $dayEnd])->count();
        $aov = $orders > 0 ? $revenue / $orders : 0;

        return DB::table('analytics_summaries')->updateOrInsert(
            ['date' => $dayStart->toDateString()],
            [
                'revenue' => $revenue,
                'visits' => $visitorStats['total_visits'],
                'unique_visitors' => $visitorStats['unique_visitors'],
                'bounce_rate' => $visitorStats['bounce_rate'],
                'avg_duration_sec' => $visitorStats['avg_duration_sec'],
                'aov' => $aov,
                'orders' => $orders,
                'updated_at' => now()
            ]
        );
    }
}

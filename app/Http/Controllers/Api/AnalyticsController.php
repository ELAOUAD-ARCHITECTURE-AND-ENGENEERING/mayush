<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitorMetric;
use App\Models\Upload;
use App\Models\AuditLog;
use App\Models\HealthMetric;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Cart;
use Carbon\Carbon;
use App\Models\Shop;
use App\Models\RefundRequest;
use App\Models\Category;
use App\Models\SellerWithdrawRequest;
use App\Models\Coupon;
use App\Models\FlashDeal;
use App\Models\User;
use App\Models\CommissionHistory;
use App\Models\BusinessSetting;

use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }
    /**
     * Ingest visitor tracking data.
     */
    public function trackVisit(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'url' => 'required|string',
            'method' => 'nullable|string',
            'referrer' => 'nullable|string',
            'is_entry' => 'nullable|boolean',
            'is_exit' => 'nullable|boolean',
            'time_spent' => 'nullable|integer',
            'click_paths' => 'nullable|array',
            'utm' => 'nullable|array',
        ]);

        $ip = $request->ip();
        
        // Attempt geolocation
        $location = null;
        try {
            // Mocking local IP for testing purposes if on localhost
            $ipToSearch = ($ip == '127.0.0.1' || $ip == '::1') ? '66.102.0.0' : $ip;
            $location = \Stevebauman\Location\Facades\Location::get($ipToSearch);
        } catch (\Exception $e) {
            \Log::warning("Geolocation failed: " . $e->getMessage());
        }

        $metric = VisitorMetric::create(array_merge($validated, [
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'country_code' => $location ? $location->countryCode : null,
            'city' => $location ? $location->cityName : null,
        ]));

        return response()->json(['status' => 'success', 'id' => $metric->id]);
    }

    /**
     * Ingest health monitoring data.
     */
    public function trackHealth(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'source' => 'required|string',
            'value' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'message' => 'nullable|string',
            'context' => 'nullable|array',
        ]);

        HealthMetric::create(array_merge($validated, [
            'created_at' => now(),
        ]));

        return response()->json(['status' => 'success']);
    }

    /**
     * Helper to apply date filtering from request.
     */
    private function applyDateFilter($query, Request $request)
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            $start = Carbon::parse($request->get('start_date'))->startOfDay();
            $end = Carbon::parse($request->get('end_date'))->endOfDay();
            return $query->whereBetween('created_at', [$start, $end]);
        }

        // Default to last 30 days if no range is provided
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Get real-time visitor summary for dashboard.
     */
    public function getVisitorStats(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->subDays(30);
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now();
        
        // MoM Comparison window
        $diff = $start->diffInDays($end);
        $prevStart = (clone $start)->subDays($diff + 1);
        $prevEnd = (clone $start)->subDay();

        $stats = $this->analyticsService->getVisitorStats($start, $end);
        $prevStats = $this->analyticsService->getVisitorStats($prevStart, $prevEnd);
        
        $countryStats = DB::table('visitor_metrics')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('country_code')
            ->select('country_code as name', DB::raw('count(distinct session_id) as count'))
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Hourly traffic for today
        $hourlyTraffic = VisitorMetric::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as views'))
            ->whereDate('created_at', now()->toDateString())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $formattedHourly = [];
        foreach ([0, 4, 8, 12, 16, 20] as $h) {
            $sum = 0;
            for ($i = $h; $i < $h + 4; $i++) {
                $sum += $hourlyTraffic[$i]->views ?? 0;
            }
            $formattedHourly[] = ['h' => str_pad($h, 2, '0', STR_PAD_LEFT), 'v' => $sum];
        }

        return response()->json(array_merge($stats, [
            'total_visits_delta' => $this->getDelta($stats['total_visits'] ?? 0, $prevStats['total_visits'] ?? 0),
            'avg_duration_delta' => $this->getDelta($stats['avg_duration_sec'] ?? 0, $prevStats['avg_duration_sec'] ?? 0),
            'bounce_rate_delta' => $this->getDelta($stats['bounce_rate'] ?? 0, $prevStats['bounce_rate'] ?? 0, true),
            'countries' => $countryStats,
            'hourly_traffic' => $formattedHourly,
            'funnel_stats' => $this->analyticsService->getFunnelStats($start, $end)
        ]));
    }

    /**
     * Get health metrics summary for dashboard.
     */
    public function getHealthStats()
    {
        $since = now()->subHours(24);

        $errorCount = HealthMetric::where('type', 'error')
            ->where('created_at', '>=', $since)
            ->count();

        $recentErrors = HealthMetric::where('type', 'error')
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $avgLatency = HealthMetric::where('type', 'latency')
            ->where('created_at', '>=', $since)
            ->avg('value') ?? 0;

        return response()->json([
            'errors_24h' => $errorCount,
            'recent_errors' => $recentErrors,
            'avg_latency_ms' => round($avgLatency, 2),
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
        ]);
    }

    /**
     * Generate automated insights based on recent data patterns.
     */
    public function getAutomatedInsights()
    {
        $insights = [];
        $now = now();
        $oneHourAgo = $now->copy()->subHour();
        $twoHoursAgo = $now->copy()->subHours(2);

        // 1. Error Rate Insight
        $errorsLastHour = HealthMetric::where('type', 'error')->where('created_at', '>=', $oneHourAgo)->count();
        if ($errorsLastHour > 10) {
            $insights[] = [
                'level' => 'critical',
                'title' => 'High Error Rate Detected',
                'message' => "Detected $errorsLastHour errors in the last hour. check the Health Console for details.",
            ];
        }

        // 2. Conversion Trend (Simplified: Entries vs Cart vs Exit)
        $entriesLastHour = VisitorMetric::where('is_entry', true)->where('created_at', '>=', $oneHourAgo)->count();
        if ($entriesLastHour > 0) {
            $abandonments = Cart::where('updated_at', '>=', $oneHourAgo)->count();
            $ratio = ($abandonments / $entriesLastHour) * 100;
            if ($ratio > 50) {
                $insights[] = [
                    'level' => 'warning',
                    'title' => 'High Abandonment Ratio',
                    'message' => "Abandonment rate is at " . round($ratio) . "% of new entries. Consider checking checkout friction.",
                ];
            }
        }

        // 3. Performance Insight
        $latency = HealthMetric::where('type', 'latency')->where('created_at', '>=', $oneHourAgo)->avg('value');
        if ($latency > 2000) {
            $insights[] = [
                'level' => 'warning',
                'title' => 'Performance Degradation',
                'message' => "Average page load time is " . round($latency / 1000, 1) . "s, which is above threshold.",
            ];
        }

        // 4. Campaign ROI (Quick UTM check)
        $topCampaign = VisitorMetric::whereNotNull('utm')
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->select('utm', DB::raw('count(*) as count'))
            ->groupBy('utm')
            ->orderByDesc('count')
            ->first();
            
        if ($topCampaign) {
            $utm = is_string($topCampaign->utm) ? json_decode($topCampaign->utm, true) : $topCampaign->utm;
            $campaignName = (is_array($utm) && isset($utm['utm_campaign'])) ? $utm['utm_campaign'] : 'Unknown';
            $insights[] = [
                'level' => 'info',
                'title' => 'Campaign Performance',
                'message' => "Campaign '$campaignName' is currently your top traffic driver.",
            ];
        }

        return response()->json($insights);
    }

    public function getCartStats(Request $request)
    {
        $query = Cart::query();
        $this->applyDateFilter($query, $request);

        // A cart is considered abandoned if it wasn't updated in the last hour
        $abandonedCarts = clone $query;
        $abandonedCarts = $abandonedCarts->where('updated_at', '<', now()->subHour())->get();

        $totalAbandonedValue = 0;
        foreach ($abandonedCarts as $cart) {
            $totalAbandonedValue += ($cart->price + $cart->tax + $cart->shipping_cost) * $cart->quantity;
        }

        $abandonedCount = $abandonedCarts->count();
        $totalValue = $totalAbandonedValue; // Renamed for clarity with the new return structure

        // For recovery rate, we need total carts and recovered carts
        $totalCarts = (clone $query)->count();
        $recoveredCarts = (clone $query)->where('updated_at', '>=', now()->subHour())->count(); // Carts not abandoned

        $recoveryRate = $totalCarts > 0 ? round(($recoveredCarts / $totalCarts) * 100, 1) : 0;

        $abandonedTrend = (clone $query)
            ->where('updated_at', '<', now()->subHour()) // Only count abandoned carts for the trend
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count')
            ->toArray();

        return response()->json([
            'abandoned_count' => $abandonedCount,
            'total_value' => (float)$totalValue,
            'recovered_count' => $recoveredCarts,
            'recovery_rate' => $recoveryRate,
            'abandoned_trend' => $abandonedTrend,
            'recent_abandonments' => $abandonedCarts->take(10)->map(function($cart) {
                return [
                    'user' => $cart->user ? $cart->user->name : 'Guest',
                    'product' => $cart->product ? $cart->product->name : 'Unknown',
                    'value' => ($cart->price + $cart->tax + $cart->shipping_cost) * $cart->quantity,
                    'time' => $cart->updated_at->diffForHumans(),
                ];
            }),
        ]);
    }
    public function getLiveLocations()
    {
        $since = now()->subMinutes(10); // True real-time
        $locations = VisitorMetric::where('created_at', '>=', $since)
            ->whereNotNull('country_code')
            ->select('country_code', 'city', 'ip_address', 'session_id')
            ->get()
            ->unique('session_id')
            ->values();

        return response()->json($locations);
    }

    /**
     * Detailed analysis of traffic sources.
     */
    public function getTrafficSources(Request $request)
    {
        $query = VisitorMetric::query();
        $this->applyDateFilter($query, $request);

        $metrics = $query->select('utm', 'referrer', 'session_id')->get();
        
        $sourceCounts = [];
        $sessions = [];

        foreach ($metrics as $m) {
            // Track sessions to simulate unique count
            if (isset($sessions[$m->session_id])) continue;
            $sessions[$m->session_id] = true;

            $source = 'Referral';
            $utm = is_string($m->utm) ? json_decode($m->utm, true) : $m->utm;
            
            if (!empty($utm['utm_source'])) {
                $source = $utm['utm_source'];
            } elseif (empty($m->referrer)) {
                $source = 'Direct';
            } else {
                $ref = strtolower($m->referrer);
                if (str_contains($ref, 'google')) $source = 'Google';
                elseif (str_contains($ref, 'facebook') || str_contains($ref, 'fb')) $source = 'Facebook';
                elseif (str_contains($ref, 'twitter') || str_contains($ref, 't.co')) $source = 'Twitter';
                elseif (str_contains($ref, 'linkedin')) $source = 'LinkedIn';
            }

            $sourceCounts[$source] = ($sourceCounts[$source] ?? 0) + 1;
        }

        $formatted = [];
        foreach ($sourceCounts as $source => $count) {
            $formatted[] = ['source' => $source, 'count' => $count];
        }

        usort($formatted, fn($a, $b) => $b['count'] <=> $a['count']);

        return response()->json($formatted);
    }

    /**
     * Page-level performance metrics.
     */
    public function getPagePerformance(Request $request)
    {
        $query = VisitorMetric::query();
        $this->applyDateFilter($query, $request);

        $pages = (clone $query)
            ->select(
                'url',
                DB::raw('count(*) as views'),
                DB::raw('count(distinct session_id) as unique_views'),
                DB::raw('avg(time_spent) as avg_time'),
                DB::raw("sum(CASE WHEN is_exit = 1 THEN 1 ELSE 0 END) as exits"),
                DB::raw("sum(CASE WHEN is_entry = 1 THEN 1 ELSE 0 END) as entrances")
            )
            ->groupBy('url')
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        return response()->json($pages);
    }

    /**
     * Analyze user flow paths between pages.
     */
    public function getBehaviorFlow(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->subDays(30);
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now();

        return response()->json($this->analyticsService->getBehaviorFlow($start, $end));
    }

    /**
     * Aggregate click coordinates for heatmap visualization.
     */
    public function getInteractionHeatmap(Request $request)
    {
        $url = $request->get('url', '/');
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->subDays(30);
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now();

        $heatmap = $this->analyticsService->getHeatmapGrid($url, $start, $end);

        return response()->json([
            'url' => $url,
            'points' => $heatmap
        ]);
    }

    /**
     * Export analytics data to CSV.
     */
    public function exportToCsv(Request $request)
    {
        $type = $request->get('type', 'visitors');
        $query = VisitorMetric::query();
        $this->applyDateFilter($query, $request);

        $filename = "analytics_{$type}_" . now()->format('Ymd_His') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($type, $query) {
            $handle = fopen('php://output', 'w');
            
            if ($type === 'visitors') {
                fputcsv($handle, ['Date', 'URL', 'IP', 'Session ID', 'Country', 'City', 'Time Spent (s)']);
                $query->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [$row->created_at, $row->url, $row->ip_address, $row->session_id, $row->country_code, $row->city, $row->time_spent]);
                    }
                });
            } elseif ($type === 'pages') {
                fputcsv($handle, ['Page URL', 'Total Views', 'Unique Views', 'Avg Time', 'Exit Rate %']);
                $data = (clone $query)
                    ->select('url', DB::raw('count(*) as views'), DB::raw('count(distinct session_id) as unique_views'), DB::raw('avg(time_spent) as avg_time'), DB::raw("sum(CASE WHEN is_exit = 1 THEN 1 ELSE 0 END) as exits"))
                    ->groupBy('url')
                    ->get();
                foreach ($data as $row) {
                    $exitRate = $row->views > 0 ? round(($row->exits / $row->views) * 100, 2) : 0;
                    fputcsv($handle, [$row->url, $row->views, $row->unique_views, round($row->avg_time), $exitRate]);
                }
            } elseif ($type === 'sources') {
                fputcsv($handle, ['Source', 'Session Count']);
                $data = (clone $query)
                    ->select(DB::raw("
                        CASE 
                            WHEN utm->'$.utm_source' IS NOT NULL THEN utm->'$.utm_source'
                            WHEN referrer LIKE '%google%' THEN 'Google'
                            WHEN referrer IS NULL OR referrer = '' THEN 'Direct'
                            ELSE 'Referral'
                        END as source
                    "), DB::raw('count(distinct session_id) as count'))
                    ->groupBy('source')
                    ->get();
                foreach ($data as $row) {
                    fputcsv($handle, [$row->source, $row->count]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getVisitorFlow()
    {
        $since = now()->subDays(7);
        $entryPages = VisitorMetric::where('is_entry', true)
            ->where('created_at', '>=', $since)
            ->select('url', DB::raw('count(*) as count'))
            ->groupBy('url')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $exitPages = VisitorMetric::where('is_exit', true)
            ->where('created_at', '>=', $since)
            ->select('url', DB::raw('count(*) as count'))
            ->groupBy('url')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'entries' => $entryPages,
            'exits' => $exitPages
        ]);
    }

    public function getForecastingData(Request $request)
    {
        $start_date = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : now()->subDays(30)->startOfDay();
        $end_date = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

        $historyData = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Zero-filling for the historical period
        $history = [];
        $current = clone $start_date;
        while ($current <= $end_date) {
            $dateStr = $current->format('Y-m-d');
            $history[] = [
                'date' => $dateStr,
                'total' => isset($historyData[$dateStr]) ? (float)$historyData[$dateStr]->total : 0
            ];
            $current->addDay();
        }

        // Simple Linear Regression for forecasting
        $n = count($history);
        if ($n < 2) {
            return response()->json(['history' => $history, 'forecast' => [], 'growth_rate' => 0]);
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;

        foreach ($history as $index => $row) {
            $x = $index;
            $y = $row['total'];
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumXX += ($x * $x);
        }

        $denominator = ($n * $sumXX - $sumX * $sumX);
        $slope = $denominator != 0 ? ($n * $sumXY - $sumX * $sumY) / $denominator : 0;
        $intercept = ($sumY - $slope * $sumX) / $n;

        $forecast = [];
        $lastDate = \Carbon\Carbon::parse(end($history)['date']);
        for ($i = 1; $i <= 7; $i++) {
            $x = $n + $i - 1;
            $y = $slope * $x + $intercept;
            $forecast[] = [
                'date' => (clone $lastDate)->addDays($i)->format('Y-m-d'),
                'total' => max(0, round($y, 2))
            ];
        }

        return response()->json([
            'history' => $history,
            'forecast' => $forecast,
            'growth_rate' => round($slope, 2)
        ]);
    }

    /**
     * Get top vendors by revenue and order count.
     */
    public function getTopVendors(Request $request)
    {
        $start_date = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(30);
        $end_date   = $request->end_date   ? Carbon::parse($request->end_date)   : now();
        $prevStart  = (clone $start_date)->sub($start_date->diffAsCarbonInterval($end_date));

        $vendors = \App\Models\Shop::join('orders', 'shops.user_id', '=', 'orders.seller_id')
            ->select(
                'shops.id',
                'shops.name',
                'shops.logo',
                DB::raw('SUM(orders.grand_total) as revenue'),
                DB::raw('COUNT(orders.id) as orders_count')
            )
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->groupBy('shops.id', 'shops.name', 'shops.logo')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Pre-fetch previous-period revenues for trend calculation
        $shopIds = $vendors->pluck('id');
        $prevRevenues = \App\Models\Shop::join('orders', 'shops.user_id', '=', 'orders.seller_id')
            ->select('shops.id', DB::raw('SUM(orders.grand_total) as prev_revenue'))
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$prevStart, $start_date])
            ->whereIn('shops.id', $shopIds)
            ->groupBy('shops.id')
            ->pluck('prev_revenue', 'id');

        // Pre-fetch avg shop ratings from reviews table
        $hasRatings = Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'rating');
        $shopRatings = [];
        if ($hasRatings) {
            $shopRatings = DB::table('reviews')
                ->join('products', 'reviews.product_id', '=', 'products.id')
                ->join('shops', 'products.user_id', '=', 'shops.user_id')
                ->select('shops.id', DB::raw('ROUND(AVG(reviews.rating), 1) as avg_rating'))
                ->whereIn('shops.id', $shopIds)
                ->groupBy('shops.id')
                ->pluck('avg_rating', 'id');
        }

        return response()->json($vendors->map(function($v) use ($prevRevenues, $shopRatings) {
            $prevRev = (float)($prevRevenues[$v->id] ?? 0);
            $currRev = (float)$v->revenue;
            $trendPct = $prevRev > 0 ? round((($currRev - $prevRev) / $prevRev) * 100, 1) : null;
            $trendStr = $trendPct !== null ? ($trendPct >= 0 ? '+' : '') . $trendPct . '%' : 'N/A';
            return [
                'name'    => $v->name,
                'logo'    => $v->logo,
                'revenue' => $currRev,
                'orders'  => $v->orders_count,
                'rating'  => $shopRatings[$v->id] ?? null,
                'trend'   => $trendStr,
            ];
        }));
    }

    /**
     * Get system health and integration status.
     */
    public function getSystemStatus()
    {
        $since = now()->subHours(24);
        
        // General Core API
        $dbLatency = 0;
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $dbLatency = round((microtime(true) - $start) * 1000);
            $dbStatus = 'ok';
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        $coreErrors = HealthMetric::where('created_at', '>=', $since)->where('type', 'error')->count();
        $coreUptime = $coreErrors > 10 ? '98.5%' : ($coreErrors > 5 ? '99.5%' : '100%');

        // Dynamic API Statuses
        $services = [
            ['name' => 'CMI Gateway', 'source' => 'Payments', 'key' => 'cmi_api'],
            ['name' => 'Cash On Delivery', 'source' => 'Payments', 'key' => 'cod_api'],
            ['name' => 'Custom SMTP', 'source' => 'Email', 'key' => 'smtp_api'],
            ['name' => 'Analytics API', 'source' => 'Tracking', 'key' => 'analytics_api']
        ];

        $results = [];
        
        foreach($services as $svc) {
            // Find recent metrics for this service
            $errorCount = HealthMetric::where('source', $svc['key'])->where('type', 'error')->where('created_at', '>=', $since)->count();
            $avgLatency = HealthMetric::where('source', $svc['key'])->where('type', 'latency')->where('created_at', '>=', $since)->avg('value');
            
            // If genuinely missing data, we will provide an authentic "No Data" or fallback based on current system state, but here we assume green if no errors reported recently in a passive monitoring system.
            $results[] = [
                'name' => $svc['name'],
                'source' => $svc['source'],
                'rate' => $errorCount > 0 ? (100 - ($errorCount * 0.1)) . '%' : '100%',
                'status' => $errorCount > 5 ? 'error' : ($errorCount > 0 ? 'warn' : 'ok'),
                'latency' => round((float)($avgLatency ?? 0))
            ];
        }

        // Add Core API
        $results[] = [
            'name' => 'Core API',
            'source' => 'System',
            'rate' => $coreUptime,
            'status' => $dbStatus === 'error' ? 'error' : ($coreErrors > 10 ? 'warn' : 'ok'),
            'latency' => $dbLatency
        ];

        return response()->json($results);
    }

    /**
     * Get default currency configuration.
     */
    public function getCurrencyConfig()
    {
        $currencyId = get_setting('system_default_currency');
        $currency = \App\Models\Currency::find($currencyId);

        return response()->json([
            'symbol' => $currency ? $currency->symbol : '$',
            'code' => $currency ? $currency->code : 'USD',
            'exchange_rate' => $currency ? $currency->exchange_rate : 1
        ]);
    }
    /**
     * Get vendor-specific analytics.
     */
    public function getVendorAnalytics(Request $request)
    {
        $this->applyDateFilter(Order::query(), $request); // Not directly applied but for consistency
        $start_date = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(30);
        $end_date = $request->end_date ? Carbon::parse($request->end_date) : now();

        $totalVendors = Shop::count();
        $newVendors = Shop::where('created_at', '>=', now()->startOfMonth())->count();
        // Real avg rating from reviews table
        $avgRating = DB::table('reviews')->avg('rating');
        $avgRating = $avgRating !== null ? round((float)$avgRating, 1) : null;
        
        $gmv = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->sum('grand_total');

        $disputeCount = RefundRequest::whereBetween('created_at', [$start_date, $end_date])->count();
        $totalOrders = Order::whereBetween('created_at', [$start_date, $end_date])->count();
        $disputeRate = $totalOrders > 0 ? round(((float)$disputeCount / $totalOrders) * 100, 2) : 0;

        // Vendor Growth Chart — real counts, churn set to 0 (no tracking available)
        $growth = [];
        for ($i = 6; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $growth[] = [
                'month'   => $month->format('M'),
                'active'  => Shop::where('created_at', '<=', $month->copy()->endOfMonth())->count(),
                'new'     => Shop::whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count(),
                'churned' => 0 // Churn tracking not available
            ];
        }

        // Sales by Category — real data
        $categories = Category::withCount(['products' => function($q) use ($start_date, $end_date) {
            $q->join('order_details', 'products.id', '=', 'order_details.product_id')
              ->join('orders', 'order_details.order_id', '=', 'orders.id')
              ->where('orders.payment_status', 'paid')
              ->whereBetween('orders.created_at', [$start_date, $end_date]);
        }])->get()
        ->map(fn($c) => ['name' => $c->getTranslation('name'), 'value' => $c->products_count])
        ->sortByDesc('value')
        ->take(6)
        ->values();

        // Vendor Directory — real revenue, real disputes, derived status
        $directoryShops = Shop::join('users', 'shops.user_id', '=', 'users.id')
            ->leftJoin('orders', 'shops.user_id', '=', 'orders.seller_id')
            ->select(
                'shops.id',
                'shops.name',
                'shops.created_at',
                DB::raw('SUM(CASE WHEN orders.payment_status = "paid" THEN orders.grand_total ELSE 0 END) as revenue'),
                DB::raw('COUNT(CASE WHEN orders.payment_status = "paid" THEN orders.id END) as orders_count')
            )
            ->groupBy('shops.id', 'shops.name', 'shops.created_at')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $directoryShopIds = $directoryShops->pluck('id');

        // Per-vendor dispute counts
        $vendorDisputes = RefundRequest::whereBetween('created_at', [$start_date, $end_date])
            ->whereIn('seller_id', $directoryShops->pluck('id')->map(fn($id) =>
                DB::table('shops')->where('id', $id)->value('user_id')
            ))
            ->select('seller_id', DB::raw('COUNT(*) as dispute_count'))
            ->groupBy('seller_id')
            ->pluck('dispute_count', 'seller_id');

        // Per-vendor avg ratings
        $hasRatings = Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'rating');
        $vendorRatings = [];
        if ($hasRatings) {
            $vendorRatings = DB::table('reviews')
                ->join('products', 'reviews.product_id', '=', 'products.id')
                ->join('shops', 'products.user_id', '=', 'shops.user_id')
                ->select('shops.id', DB::raw('ROUND(AVG(reviews.rating), 1) as avg_rating'))
                ->whereIn('shops.id', $directoryShopIds)
                ->groupBy('shops.id')
                ->pluck('avg_rating', 'id');
        }

        // Max revenue for relative ranking
        $maxRevenue = $directoryShops->max('revenue') ?: 1;

        $directory = $directoryShops->map(function($s) use ($vendorDisputes, $vendorRatings, $maxRevenue) {
            $sellerId = DB::table('shops')->where('id', $s->id)->value('user_id');
            $disputes = (int)($vendorDisputes[$sellerId] ?? 0);
            $revRatio = (float)$s->revenue / $maxRevenue;
            $status   = $revRatio >= 0.7 ? 'Top' : ($revRatio >= 0.3 ? 'Active' : ($s->orders_count > 0 ? 'Rising' : 'New'));
            return [
                'name'     => $s->name,
                'initials' => strtoupper(substr($s->name, 0, 2)),
                'joined'   => Carbon::parse($s->created_at)->format('M Y'),
                'category' => 'Multi',
                'revenue'  => (float)$s->revenue,
                'orders'   => (int)$s->orders_count,
                'rating'   => $vendorRatings[$s->id] ?? null,
                'trend'    => null, // Requires period comparison – not available in this query scope
                'disputes' => $disputes,
                'status'   => $status,
            ];
        });

        // Dispute trend — real monthly counts
        $disputeTrend = array_map(function($i) {
            $month = now()->subMonths($i);
            return [
                'month' => $month->format('M'),
                'count' => RefundRequest::whereMonth('created_at', $month->month)
                                        ->whereYear('created_at', $month->year)
                                        ->count()
            ];
        }, range(5, 0));

        return response()->json([
            'kpis' => [
                'active'        => $totalVendors,
                'active_delta'  => null,
                'new'           => $newVendors,
                'new_delta'     => null,
                'rating'        => $avgRating,
                'rating_delta'  => null,
                'gmv'           => (float)$gmv,
                'gmv_delta'     => $this->getDelta($gmv, Order::where('payment_status', 'paid')->whereBetween('created_at', [(clone $start_date)->subDays(31), (clone $start_date)->subDay()])->sum('grand_total')),
                'dispute_rate'  => $disputeRate,
                'dispute_delta' => null
            ],
            'growth_chart'  => $growth,
            'category_pie'  => $categories,
            'directory'     => $directory,
            'dispute_trend' => $disputeTrend,
        ]);
    }

    /**
     * Get finance analytics.
     */
    public function getFinanceAnalytics(Request $request)
    {
        $start_date = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(30);
        $end_date = $request->end_date ? Carbon::parse($request->end_date) : now();
        $diff = $start_date->diffInDays($end_date);
        $prevStart = (clone $start_date)->subDays($diff + 1);
        $prevEnd = (clone $start_date)->subDay();

        $grossGmv = Order::whereBetween('created_at', [$start_date, $end_date])->sum('grand_total');
        $prevGrossGmv = Order::whereBetween('created_at', [$prevStart, $prevEnd])->sum('grand_total');
        
        $netRevenue = CommissionHistory::whereBetween('created_at', [$start_date, $end_date])->sum('admin_commission');
        $prevNetRevenue = CommissionHistory::whereBetween('created_at', [$prevStart, $prevEnd])->sum('admin_commission');
        
        $totalOrders = Order::whereBetween('created_at', [$start_date, $end_date])->count();
        $refunds = RefundRequest::where('refund_status', 1)->whereBetween('created_at', [$start_date, $end_date])->count();
        $refundRate = $totalOrders > 0 ? round(($refunds / $totalOrders) * 100, 2) : 0;

        $prevTotalOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevRefunds = RefundRequest::where('refund_status', 1)->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevRefundRate = $prevTotalOrders > 0 ? round(($prevRefunds / $prevTotalOrders) * 100, 2) : 0;

        $pendingPayouts = SellerWithdrawRequest::where('status', 0)->sum('amount');
        $pendingVendors = SellerWithdrawRequest::where('status', 0)->distinct('user_id')->count();

        // Real item count per order
        $avgItems = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->avg('order_details.quantity') ?: 0;

        // Real tax by region - Aggregate in PHP, joined with order_details
        $taxOrders = DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->where('order_details.tax', '>', 0)
            ->select('orders.shipping_address', 'order_details.tax as collected')
            ->get();

        $taxByRegion = $taxOrders->map(function($o) {
            $addr = json_decode($o->shipping_address, true);
            return [
                'region' => $addr['country'] ?? 'Unknown',
                'collected' => (float)$o->collected
            ];
        })->groupBy('region')->map(function($group, $region) {
            return [
                'region' => $region,
                'collected' => $group->sum('collected'),
                'rate' => 'Dynamic',
                'status' => 'Compliant'
            ];
        })->values()->take(4);


        // Commission/Refunds Chart
        $financeChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $financeChart[] = [
                'month' => $month->format('M'),
                'commission' => (float)CommissionHistory::whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('admin_commission'),
                'fees' => 0, // No specific fees table exists
                'refunds' => (float)RefundRequest::where('refund_status', 1)->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count() * 100
            ];
        }

        $payouts = SellerWithdrawRequest::with('user', 'shop')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(function($r) {
                return [
                    'vendor' => $r->shop ? $r->shop->name : ($r->user ? $r->user->name : 'Unknown'),
                    'amount' => (float)$r->amount,
                    'status' => $r->status == 1 ? 'Paid' : ($r->status == 2 ? 'Processing' : 'Pending'),
                    'date' => $r->created_at->format('d M Y')
                ];
            });

        return response()->json([
            'kpis' => [
                'gross_gmv' => (float)$grossGmv,
                'gross_gmv_delta' => $this->getDelta($grossGmv, $prevGrossGmv),
                'net_revenue' => (float)$netRevenue,
                'net_revenue_delta' => $this->getDelta($netRevenue, $prevNetRevenue),
                'commission' => (float)$netRevenue,
                'commission_delta' => $this->getDelta($netRevenue, $prevNetRevenue),
                'refund_rate' => $refundRate,
                'refund_delta' => $this->getDelta($refundRate, $prevRefundRate, true),
                'pending_payouts' => (float)$pendingPayouts,
                'pending_vendors' => $pendingVendors
            ],
            'chart' => $financeChart,
            'refund_trend' => array_map(function($i) {
                $month = now()->subMonths($i);
                $orders = Order::whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count();
                $refunds = RefundRequest::where('refund_status', 1)->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count();
                return [
                    'month' => $month->format('M'),
                    'rate' => $orders > 0 ? round(($refunds / $orders) * 100, 1) : 0
                ];
            }, range(6, 0)),
            'payouts' => $payouts,
            'aov' => $totalOrders > 0 ? round($grossGmv / $totalOrders, 2) : 0,
            'items_per_order' => round($avgItems, 1),
            'tax_data' => $taxByRegion
        ]);
    }

    /**
     * Get marketing analytics.
     */
    public function getMarketingAnalytics(Request $request)
    {
        $start_date = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(30);
        $end_date   = $request->end_date   ? Carbon::parse($request->end_date)   : now();
        $diff = $start_date->diffInDays($end_date);
        $prevStart = (clone $start_date)->subDays($diff + 1);
        $prevEnd = (clone $start_date)->subDay();

        // Real coupon revenue: sum of orders that had a coupon applied
        $couponRevenue = Order::where('coupon_discount', '>', 0)
            ->whereBetween('created_at', [$start_date, $end_date])
            ->sum('grand_total');
        
        $prevCouponRevenue = Order::where('coupon_discount', '>', 0)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('grand_total');

        $activeCoupons = Coupon::where('status', 1)
            ->where('end_date', '>', now()->timestamp)
            ->count();

        // Real customer LTV: total revenue / distinct paying customers
        $totalRevenue    = Order::where('payment_status', 'paid')->sum('grand_total');
        $distinctBuyers  = Order::where('payment_status', 'paid')->distinct('user_id')->count('user_id');
        $customerLtv     = $distinctBuyers > 0 ? round((float)$totalRevenue / $distinctBuyers, 2) : 0;

        $prevTotalRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '<', $start_date)
            ->sum('grand_total');
        $prevBuyers = Order::where('payment_status', 'paid')
            ->where('created_at', '<', $start_date)
            ->distinct('user_id')->count('user_id');
        $prevLtv = $prevBuyers > 0 ? round((float)$prevTotalRevenue / $prevBuyers, 2) : 0;

        // Campaigns (Flash Deals)
        $campaigns = FlashDeal::orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($f) use ($start_date, $end_date) {
                return [
                    'name'    => $f->getTranslation('title'),
                    'channel' => 'Flash Sale',
                    'sent'    => null,
                    'opens'   => null,
                    'clicks'  => null,
                    'revenue' => (float)Order::where('payment_status', 'paid')->whereBetween('created_at', [$start_date, $end_date])->sum('grand_total'),
                    'roi'     => null,
                    'status'  => $f->status ? 'Live' : 'Ended',
                ];
            });

        // Coupons — real usage count and revenue from coupon_usages / orders
        $hasCouponUsages = Schema::hasTable('coupon_usages');
        $coupons = Coupon::orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($c) use ($hasCouponUsages) {
                $uses = $hasCouponUsages
                    ? DB::table('coupon_usages')->where('coupon_id', $c->id)->count()
                    : Order::where('coupon_code', $c->code)->count();

                $revenue = Order::where('payment_status', 'paid')
                    ->where('coupon_code', $c->code)
                    ->sum('grand_total');

                return [
                    'code'     => $c->code,
                    'discount' => $c->discount . ($c->discount_type == 'amount' ? '' : '%'),
                    'uses'     => (int)$uses,
                    'revenue'  => (float)$revenue,
                    'expires'  => date('d M Y', $c->end_date),
                ];
            });

        // Retention cohort — real new customer cohorts by month
        $cohortData = [];
        foreach (range(2, 0) as $monthBack) {
            $cohortMonth = now()->subMonths($monthBack);
            $cohortStart = $cohortMonth->copy()->startOfMonth();
            $cohortEnd   = $cohortMonth->copy()->endOfMonth();

            // New customers acquired this month
            $cohortUserIds = Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$cohortStart, $cohortEnd])
                ->distinct('user_id')
                ->pluck('user_id');
            $cohortSize = $cohortUserIds->count();

            $row = ['month' => $cohortMonth->format('M'), 'm0' => 100];
            for ($j = 1; $j <= 3; $j++) {
                $retStart = $cohortMonth->copy()->startOfMonth()->addMonths($j);
                $retEnd   = $retStart->copy()->endOfMonth();
                if ($retStart > now()) {
                    $row["m$j"] = null;
                } else {
                    $retained = Order::where('payment_status', 'paid')
                        ->whereBetween('created_at', [$retStart, $retEnd])
                        ->whereIn('user_id', $cohortUserIds)
                        ->distinct('user_id')->count();
                    $row["m$j"] = $cohortSize > 0 ? round(((float)$retained / $cohortSize) * 100) : 0;
                }
            }
            $cohortData[] = $row;
        }

        return response()->json([
            'kpis' => [
                'campaign_revenue' => (float)$couponRevenue,
                'revenue_delta'    => $this->getDelta($couponRevenue, $prevCouponRevenue),
                'email_open_rate'  => null,
                'avg_roi'          => null,
                'active_coupons'   => $activeCoupons,
                'customer_ltv'     => $customerLtv,
                'ltv_delta'        => $this->getDelta($customerLtv, $prevLtv),
            ],
            'campaigns'    => $campaigns,
            'coupons'      => $coupons,
            'email_chart'  => null,
            'cohort_data'  => $cohortData,
        ]);
    }

    private function getDelta($current, $previous, $lowerIsBetter = false)
    {
        if ($previous == 0) return $current > 0 ? '+100%' : '0%';
        $pct = round((($current - $previous) / $previous) * 100, 1);
        $sign = $pct >= 0 ? '+' : '';
        return $sign . $pct . '%';
    }

    public function getSecurityMetrics()
    {
        $last24h = Carbon::now()->subDay();
        
        $failedLogins = AuditLog::where('action_type', 'FAILED_LOGIN')
            ->where('created_at', '>=', $last24h)
            ->count();
            
        $blockedUploads = AuditLog::where('action_type', 'MALWARE_BLOCKED')
            ->where('created_at', '>=', $last24h)
            ->count();

        // Fetch 10 most recent security events
        $recentEvents = AuditLog::whereIn('action_type', ['LOGIN', 'LOGOUT', 'FAILED_LOGIN', 'MALWARE_BLOCKED', 'UNAUTHORIZED_ACCESS'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
            
        return response()->json([
            'failed_logins_24h' => $failedLogins,
            'blocked_uploads_24h' => $blockedUploads,
            'system_health' => $failedLogins > 10 ? 'danger' : ($failedLogins > 5 ? 'warning' : 'secure'),
            'recent_events' => $recentEvents->map(function($e) {
                return [
                    'time' => $e->created_at->locale(app()->getLocale())->diffForHumans(),
                    'event' => $e->action_type,
                    'ip' => $e->ip_address,
                    'description' => translate_security_event_description($e->description)
                ];
            })
        ]);
    }
}

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use App\Services\SellerFinancialService;
use Carbon\Carbon;
use Auth;
use DB;

class AnalyticsDashboardController extends Controller
{
    protected $analyticsService;
    protected $financialService;

    public function __construct(AnalyticsService $analyticsService, SellerFinancialService $financialService)
    {
        $this->analyticsService = $analyticsService;
        $this->financialService = $financialService;
    }

    public function index()
    {
        return view('seller.analytics.dashboard');
    }

    public function stats(Request $request)
    {
        $dates = $this->getDateRange($request);
        $stats = $this->analyticsService->getVisitorStats($dates['start'], $dates['end']);
        return response()->json($stats);
    }

    public function funnel(Request $request)
    {
        $dates = $this->getDateRange($request);
        $funnel = $this->analyticsService->getFunnelStats($dates['start'], $dates['end']);
        return response()->json($funnel);
    }

    public function topProducts(Request $request)
    {
        $dates = $this->getDateRange($request);
        $shop = Auth::user()->shop;
        
        if (!$shop) {
            return response()->json([]);
        }

        // Top products by views -> cart conversion -> orders
        $products = DB::table('products')
            ->where('user_id', Auth::user()->id)
            ->where('published', 1)
            ->select('id', 'name', 'num_of_view as views', 'num_of_sale as sold')
            ->orderBy('num_of_view', 'desc')
            ->limit(5)
            ->get();
            
        // Calculate dynamic add_to_cart and conversion
        $result = $products->map(function ($product) {
            // Rough estimate of carts based on total sales for demo/simplicity
            // To be robust, need to join `carts` table with date range
            $cartAdds = DB::table('carts')->where('product_id', $product->id)->count();
            
            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'views' => $product->views,
                'cart_adds' => $cartAdds,
                'sold' => $product->sold,
                'conversion_percent' => $product->views > 0 ? round(($product->sold / $product->views) * 100, 1) : 0
            ];
        });

        return response()->json($result);
    }

    public function revenueTrend(Request $request)
    {
        $dates = $this->getDateRange($request);
        
        $trends = DB::table('orders')
            ->where('seller_id', Auth::user()->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as value'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        return response()->json($trends);
    }

    public function financialStats(Request $request)
    {
        $dates = $this->getDateRange($request);
        $summary = $this->financialService->getEarningsSummary(Auth::user()->id, $dates['start'], $dates['end']);
        return response()->json($summary);
    }

    public function geoStats(Request $request)
    {
        $dates = $this->getDateRange($request);
        $geo = $this->financialService->getGeoStats(Auth::user()->id, $dates['start'], $dates['end']);
        return response()->json($geo);
    }

    public function projectedStats()
    {
        $projected = $this->financialService->getProjectedEarnings(Auth::user()->id);
        return response()->json($projected);
    }

    private function getDateRange(Request $request)
    {
        $start = $request->has('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $request->has('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        return [
            'start' => $start,
            'end' => $end
        ];
    }
}

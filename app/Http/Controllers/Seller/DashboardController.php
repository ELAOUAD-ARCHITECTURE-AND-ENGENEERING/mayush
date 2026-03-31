<?php

namespace App\Http\Controllers\Seller;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Auth;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        $authUserId = auth()->user()->id;
        $data['this_month_pending_orders'] = OrderDetail::whereSellerId($authUserId)
                                    ->whereDeliveryStatus('pending')
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->count();
        $data['this_month_cancelled_orders'] = OrderDetail::whereSellerId($authUserId)
                                    ->whereDeliveryStatus('cancelled')
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->count();
        $data['this_month_on_the_way_orders'] = OrderDetail::whereSellerId($authUserId)
                                    ->whereDeliveryStatus('on_the_way')
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->count();
        $data['this_month_delivered_orders'] = OrderDetail::whereSellerId($authUserId)
                                    ->whereDeliveryStatus('delivered')
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->count();
                                    
        $data['this_month_sold_amount'] = Order::where('seller_id', Auth::user()->id)
                                    ->wherePaymentStatus('paid')
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->sum('grand_total');
        $data['previous_month_sold_amount'] = Order::where('seller_id', Auth::user()->id)
                                    ->wherePaymentStatus('paid')
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->whereMonth('created_at', (Carbon::now()->month-1))
                                    ->sum('grand_total');
        
        $data['products'] = filter_products(Product::where('user_id', Auth::user()->id)->orderBy('num_of_sale', 'desc'))->limit(12)->get();
        
        // Phase 2b: Seller Analytics & Insights
        $sellerProducts = Product::where('user_id', $authUserId);
        
        $data['total_views'] = $sellerProducts->sum('num_of_view');
        $data['total_sales_count'] = $sellerProducts->sum('num_of_sale');
        $data['avg_conversion_rate'] = $data['total_views'] > 0 ? ($data['total_sales_count'] / $data['total_views']) * 100 : 0;
        
        // Actionable Insights: Low Stock
        $data['low_stock_products'] = Product::where('user_id', $authUserId)
                                    ->whereRaw('current_stock <= low_stock_quantity')
                                    ->where('published', 1)
                                    ->limit(5)
                                    ->get();

        // Actionable Insights: High View but Low Conversion (Conversion < 1%)
        $data['underperforming_products'] = Product::where('user_id', $authUserId)
                                            ->where('num_of_view', '>', 20)
                                            ->whereRaw('(num_of_sale / num_of_view) < 0.01')
                                            ->orderBy('num_of_view', 'desc')
                                            ->limit(5)
                                            ->get();

        // MA-105: Inventory Velocity (Last 7 Days)
        $data['inventory_velocity'] = \App\Models\InventoryLog::where('created_at', '>=', Carbon::now()->subDays(7))
                                    ->where('quantity_delta', '<', 0)
                                    ->whereIn('product_id', Product::where('user_id', $authUserId)->pluck('id'))
                                    ->select(DB::raw("sum(ABS(quantity_delta)) as total_sold, product_id"))
                                    ->groupBy('product_id')
                                    ->orderBy('total_sold', 'desc')
                                    ->limit(5)
                                    ->get();

        $data['last_7_days_sales'] = Order::where('created_at', '>=', Carbon::now()->subDays(7))
                                ->where('seller_id', '=', Auth::user()->id)
                                ->where('delivery_status', '=', 'delivered')
                                ->select(DB::raw("sum(grand_total) as total, DATE_FORMAT(created_at, '%d %b') as date"))
                                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
                                ->get()->pluck('total', 'date');

        $data['last_7_days_views'] = \App\Models\ProductView::where('created_at', '>=', Carbon::now()->subDays(7))
                                ->whereIn('product_id', Product::where('user_id', $authUserId)->pluck('id'))
                                ->select(DB::raw("count(*) as total, DATE_FORMAT(created_at, '%d %b') as date"))
                                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
                                ->orderBy('created_at', 'asc')
                                ->get()->pluck('total', 'date');

        return view('seller.dashboard', $data);
    }
}

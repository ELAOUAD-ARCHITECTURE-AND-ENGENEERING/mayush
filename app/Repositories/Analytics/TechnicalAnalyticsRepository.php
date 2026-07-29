<?php

namespace App\Repositories\Analytics;

use App\Contracts\Analytics\TechnicalAnalyticsRepositoryInterface;
use App\DTOs\Analytics\RevenueMetricsDTO;
use App\Models\Analytics\AnalyticsDailySummary;
use App\Models\SellerWithdrawRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TechnicalAnalyticsRepository implements TechnicalAnalyticsRepositoryInterface
{
    private function getDelta($current, $previous, $lowerIsBetter = false): string
    {
        if ($previous == 0) return $current > 0 ? '+100%' : '0%';
        $pct = round((($current - $previous) / $previous) * 100, 1);
        return ($pct > 0 ? '+' : '') . $pct . '%';
    }

    private function safePeriod(Carbon $start, Carbon $end): array
    {
        $diff = $start->diffInDays($end);
        return [(clone $start)->subDays($diff + 1), (clone $start)->subDay()];
    }

    // ── FINANCE ───────────────────────────────────────────────────────────────

    public function getRevenueMetrics(Carbon $start, Carbon $end): RevenueMetricsDTO
    {
        [$prevStart, $prevEnd] = $this->safePeriod($start, $end);
        try {
            $grossGmv = \App\Models\Order::where('payment_status','paid')->whereBetween('created_at',[$start,$end])->sum('grand_total');
            $prevGrossGmv = \App\Models\Order::where('payment_status','paid')->whereBetween('created_at',[$prevStart,$prevEnd])->sum('grand_total');
            $netRevenue = DB::table('commission_history')->whereBetween('created_at',[$start,$end])->sum('admin_commission');
            $prevNetRevenue = DB::table('commission_history')->whereBetween('created_at',[$prevStart,$prevEnd])->sum('admin_commission');
            $totalOrders = \App\Models\Order::whereBetween('created_at',[$start,$end])->count();
            $refunds = \App\Models\RefundRequest::where('refund_status',1)->whereBetween('created_at',[$start,$end])->count();
            $prevOrders = \App\Models\Order::whereBetween('created_at',[$prevStart,$prevEnd])->count();
            $prevRefunds = \App\Models\RefundRequest::where('refund_status',1)->whereBetween('created_at',[$prevStart,$prevEnd])->count();
            $refundRate = $totalOrders > 0 ? round(($refunds/$totalOrders)*100,2) : 0;
            $prevRefundRate = $prevOrders > 0 ? round(($prevRefunds/$prevOrders)*100,2) : 0;
            $pendingPayouts = SellerWithdrawRequest::where('status',0)->sum('amount');
            $pendingVendors = SellerWithdrawRequest::where('status',0)->distinct('user_id')->count();
        } catch (\Exception $e) {
            Log::warning('[Analytics] Revenue metrics failed: '.$e->getMessage());
            return new RevenueMetricsDTO([]);
        }
        return new RevenueMetricsDTO([
            'gross_gmv'=>(float)$grossGmv, 'gross_gmv_delta'=>$this->getDelta($grossGmv,$prevGrossGmv),
            'net_revenue'=>(float)$netRevenue, 'net_revenue_delta'=>$this->getDelta($netRevenue,$prevNetRevenue),
            'commission'=>(float)$netRevenue, 'commission_delta'=>$this->getDelta($netRevenue,$prevNetRevenue),
            'refund_rate'=>(float)$refundRate, 'refund_delta'=>$this->getDelta($refundRate,$prevRefundRate,true),
            'pending_payouts'=>(float)$pendingPayouts, 'pending_vendors'=>$pendingVendors,
        ]);
    }

    public function getRefundTrends(Carbon $start, Carbon $end): Collection
    {
        try {
            return collect(array_map(function($i) {
                $m = now()->subMonths($i);
                $orders = \App\Models\Order::whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count();
                $refunds = \App\Models\RefundRequest::where('refund_status',1)->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count();
                return ['date'=>$m->locale(app()->getLocale())->translatedFormat('M'),'value'=>$orders>0?round(($refunds/$orders)*100,1):0];
            }, range(6,0)));
        } catch (\Exception $e) { return collect(); }
    }

    public function getPayouts(int $limit = 6): Collection
    {
        try {
            return SellerWithdrawRequest::with(['user'])->orderByDesc('created_at')->limit($limit)->get()->map(function($r) {
                $name = 'Unknown';
                if ($r->user) { $name = $r->user->name; if ($r->user->relationLoaded('shop') && $r->user->shop) $name = $r->user->shop->name; }
                return ['vendor'=>$name,'amount'=>(float)$r->amount,'status'=>$r->status==1?'Paid':($r->status==2?'Processing':'Pending'),'date'=>$r->created_at->locale(app()->getLocale())->translatedFormat('d M Y')];
            });
        } catch (\Exception $e) { return collect(); }
    }

    public function getGrossGmvTrends(Carbon $start, Carbon $end): Collection
    {
        try {
            $data = \App\Models\Order::where('payment_status','paid')->whereBetween('created_at',[$start,$end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as value'))
                ->groupBy('date')->orderBy('date')->get();
            return $data->map(fn($r)=>['date'=>$r->date,'value'=>(float)$r->value]);
        } catch (\Exception $e) { return collect(); }
    }

    public function getFinanceChart(Carbon $start, Carbon $end): array
    {
        try {
            $chart = [];
            for ($i=6;$i>=0;$i--) {
                $m = now()->subMonths($i);
                $chart[] = [
                    'month'=>$m->locale(app()->getLocale())->translatedFormat('M'),
                    'commission'=>(float)DB::table('commission_history')->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->sum('admin_commission'),
                    'refunds'=>(float)\App\Models\RefundRequest::where('refund_status',1)->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count()*100,
                ];
            }
            return $chart;
        } catch (\Exception $e) { return []; }
    }

    public function getTaxCollection(Carbon $start, Carbon $end): array
    {
        try {
            $rows = DB::table('orders')->join('order_details','orders.id','=','order_details.order_id')
                ->whereBetween('orders.created_at',[$start,$end])->where('order_details.tax','>',0)
                ->select('orders.shipping_address','order_details.tax as collected')->get();
            return $rows->map(function($o){$a=json_decode($o->shipping_address,true);return['region'=>$a['country']??'Unknown','collected'=>(float)$o->collected];})->groupBy('region')->map(function($g,$r){return['region'=>$r,'collected'=>$g->sum('collected'),'rate'=>'Dynamic','status'=>'Compliant'];})->values()->take(4)->toArray();
        } catch (\Exception $e) { return []; }
    }

    public function getProfitabilityPulse(Carbon $start, Carbon $end): array
    {
        try {
            $orders = \App\Models\Order::whereBetween('created_at',[$start,$end])->count();
            $gmv = \App\Models\Order::where('payment_status','paid')->whereBetween('created_at',[$start,$end])->sum('grand_total');
            $avgItems = DB::table('order_details')->join('orders','order_details.order_id','=','orders.id')->whereBetween('orders.created_at',[$start,$end])->avg('order_details.quantity');
            return ['aov'=>$orders>0?round($gmv/$orders,2):0,'items_per_order'=>round($avgItems??0,1)];
        } catch (\Exception $e) { return ['aov'=>0,'items_per_order'=>0]; }
    }

    // ── VISITORS & TRAFFIC ────────────────────────────────────────────────────

    public function getVisitorStats(Carbon $start, Carbon $end): array
    {
        try {
            if (!Schema::hasTable('visitor_metrics')) return $this->emptyVisitorStats();
            $s = DB::table('visitor_metrics')->whereBetween('created_at',[$start,$end])->select([
                DB::raw('COUNT(*) as total_visits'), DB::raw('COUNT(DISTINCT session_id) as unique_visitors'),
                DB::raw('AVG(CASE WHEN time_spent>0 THEN time_spent ELSE NULL END) as avg_duration'),
                DB::raw('COUNT(CASE WHEN is_entry=1 AND is_exit=1 THEN 1 ELSE NULL END) as bounces')
            ])->first();
            [$ps,$pe] = $this->safePeriod($start,$end);
            $prev = DB::table('visitor_metrics')->whereBetween('created_at',[$ps,$pe])->select([
                DB::raw('COUNT(*) as total_visits'),DB::raw('AVG(CASE WHEN time_spent>0 THEN time_spent ELSE NULL END) as avg_duration'),
                DB::raw('COUNT(CASE WHEN is_entry=1 AND is_exit=1 THEN 1 ELSE NULL END) as bounces')
            ])->first();
            $br = $s->total_visits>0?round(($s->bounces/$s->total_visits)*100,1):0;
            $pbr = $prev->total_visits>0?round(($prev->bounces/$prev->total_visits)*100,1):0;
            return [
                'total_visits'=>$s->total_visits,'unique_visitors'=>$s->unique_visitors,
                'bounce_rate'=>$br,'avg_duration_sec'=>round($s->avg_duration??0),
                'total_visits_delta'=>$this->getDelta($s->total_visits,$prev->total_visits),
                'avg_duration_delta'=>$this->getDelta($s->avg_duration??0,$prev->avg_duration??0),
                'bounce_rate_delta'=>$this->getDelta($br,$pbr,true),
            ];
        } catch (\Exception $e) { Log::warning('[Analytics] Visitor stats: '.$e->getMessage()); return $this->emptyVisitorStats(); }
    }

    private function emptyVisitorStats(): array
    {
        return ['total_visits'=>0,'unique_visitors'=>0,'bounce_rate'=>0,'avg_duration_sec'=>0,'total_visits_delta'=>'0%','avg_duration_delta'=>'0%','bounce_rate_delta'=>'0%'];
    }

    public function getTrafficComposition(Carbon $start, Carbon $end): Collection
    {
        try {
            if (!Schema::hasTable('visitor_metrics')) return collect([['source'=>'Direct','count'=>0]]);
            $metrics = DB::table('visitor_metrics')->whereBetween('created_at',[$start,$end])->select('utm','referrer','session_id')->get();
            $sources=[]; $sessions=[];
            foreach ($metrics as $m) {
                if (isset($sessions[$m->session_id])) continue;
                $sessions[$m->session_id]=true;
                $src='Referral'; $utm=is_string($m->utm)?json_decode($m->utm,true):$m->utm;
                if (!empty($utm['utm_source'])) $src=$utm['utm_source'];
                elseif (empty($m->referrer)) $src='Direct';
                else { $ref=strtolower($m->referrer); if(str_contains($ref,'google'))$src='Google';elseif(str_contains($ref,'facebook')||str_contains($ref,'fb'))$src='Facebook'; }
                $sources[$src]=($sources[$src]??0)+1;
            }
            $result=[]; foreach($sources as $s=>$c) $result[]=['source'=>$s,'count'=>$c];
            usort($result,fn($a,$b)=>$b['count']<=>$a['count']);
            return collect($result);
        } catch (\Exception $e) { return collect([['source'=>'Direct','count'=>0]]); }
    }

    public function getHourlyTraffic(): array
    {
        try {
            if (!Schema::hasTable('visitor_metrics')) return [];
            $hourly = DB::table('visitor_metrics')->whereDate('created_at',now()->toDateString())
                ->select(DB::raw('HOUR(created_at) as hour'),DB::raw('count(*) as views'))->groupBy('hour')->orderBy('hour')->pluck('views','hour');
            $result=[];
            foreach([0,4,8,12,16,20] as $h) { $sum=0; for($i=$h;$i<$h+4;$i++) $sum+=($hourly[$i]??0); $result[]=['h'=>str_pad($h,2,'0',STR_PAD_LEFT),'v'=>$sum]; }
            return $result;
        } catch (\Exception $e) { return []; }
    }

    public function getFunnelStats(Carbon $start, Carbon $end): array
    {
        try {
            if (!Schema::hasTable('visitor_metrics')) return ['visits'=>0,'product_views'=>0,'add_to_cart'=>0,'checkout'=>0,'purchased'=>0];
            $m = DB::table('visitor_metrics')->whereBetween('created_at',[$start,$end])->select([
                DB::raw('COUNT(*) as visits'),
                DB::raw('COUNT(CASE WHEN url LIKE "%/product/%" THEN 1 END) as product_views'),
                DB::raw('COUNT(CASE WHEN url LIKE "%/checkout%" THEN 1 END) as checkout')
            ])->first();
            $carts = DB::table('carts')->whereBetween('updated_at',[$start,$end])->count();
            $purchases = \App\Models\Order::where('payment_status','paid')->whereBetween('created_at',[$start,$end])->count();
            return ['visits'=>$m->visits,'product_views'=>$m->product_views,'add_to_cart'=>$carts,'checkout'=>$m->checkout,'purchased'=>$purchases];
        } catch (\Exception $e) { return ['visits'=>0,'product_views'=>0,'add_to_cart'=>0,'checkout'=>0,'purchased'=>0]; }
    }

    // ── VENDORS ───────────────────────────────────────────────────────────────

    public function getTopVendorsSnapshot(Carbon $date): Collection
    {
        try {
            return \App\Models\Shop::join('orders','shops.user_id','=','orders.seller_id')
                ->select('shops.id','shops.name',DB::raw('SUM(orders.grand_total) as total_revenue'),DB::raw('COUNT(orders.id) as total_orders'))
                ->where('orders.payment_status','paid')->groupBy('shops.id','shops.name')->orderByDesc('total_revenue')->limit(10)->get()
                ->map(function($v) {
                    $rating = null;
                    try { $rating = DB::table('reviews')->join('products','reviews.product_id','=','products.id')->join('shops','products.user_id','=','shops.user_id')->where('shops.id',$v->id)->avg('reviews.rating'); } catch(\Exception $e){}
                    return ['seller_id'=>$v->id,'seller'=>['shop'=>['name'=>$v->name]],'total_revenue'=>(float)$v->total_revenue,'total_orders'=>(int)$v->total_orders,'avg_rating'=>$rating?round($rating,1):null];
                });
        } catch (\Exception $e) { return collect(); }
    }

    public function getVendorKpis(Carbon $start, Carbon $end): array
    {
        try {
            $total = $this->approvedVendorQuery()->count();
            $new = $this->approvedVendorQuery()->whereBetween('created_at', [$start, $end])->count();
            $avgRating = DB::table('reviews')->avg('rating'); $avgRating=$avgRating?round((float)$avgRating,1):null;
            $gmv = \App\Models\Order::where('payment_status','paid')->whereBetween('created_at',[$start,$end])->sum('grand_total');
            $disputes = \App\Models\RefundRequest::whereBetween('created_at',[$start,$end])->count();
            $orders = \App\Models\Order::whereBetween('created_at',[$start,$end])->count();
            $disputeRate = $orders>0?round(($disputes/$orders)*100,2):0;
            return ['active'=>$total,'new'=>$new,'rating'=>$avgRating,'gmv'=>(float)$gmv,'dispute_rate'=>$disputeRate];
        } catch (\Exception $e) { return ['active'=>0,'new'=>0,'rating'=>null,'gmv'=>0,'dispute_rate'=>0]; }
    }

    public function getVendorGrowthChart(): array
    {
        try {
            $growth=[];
            for($i=6;$i>=0;$i--) {
                $m=now()->subMonths($i);
                $growth[]=[
                    'month'=>$m->locale(app()->getLocale())->translatedFormat('M'),
                    'active'=>$this->approvedVendorQuery()->where('created_at','<=',$m->copy()->endOfMonth())->count(),
                    'new'=>$this->approvedVendorQuery()->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count(),
                    'churned'=>0,
                ];
            }
            return $growth;
        } catch (\Exception $e) { return []; }
    }

    public function getCategoryDistribution(Carbon $start, Carbon $end): array
    {
        try {
            return \App\Models\Category::select('categories.id','categories.name')
                ->join('products','categories.id','=','products.category_id')
                ->join('order_details','products.id','=','order_details.product_id')
                ->join('orders','order_details.order_id','=','orders.id')
                ->where('orders.payment_status','paid')->whereBetween('orders.created_at',[$start,$end])
                ->groupBy('categories.id','categories.name')
                ->select('categories.id',DB::raw('COUNT(order_details.id) as value'))
                ->orderByDesc('value')->limit(6)->get()
                ->map(function($c){try{return['name'=>$c->getTranslation('name'),'value'=>$c->value];}catch(\Exception $e){return['name'=>'Cat #'.$c->id,'value'=>$c->value];}})
                ->toArray();
        } catch (\Exception $e) { return []; }
    }

    // ── MARKETING ─────────────────────────────────────────────────────────────

    public function getMarketingMetrics(Carbon $start, Carbon $end): array
    {
        try {
            $campaigns = \App\Models\FlashDeal::orderByDesc('created_at')->limit(5)->get()->map(function($f) {
                return ['name'=>$f->getTranslation('title'),'status'=>$f->status?'Live':'Ended','channel'=>'Flash Sale'];
            })->toArray();
            return ['campaign_visits'=>0,'coupon_revenue'=>(float)\App\Models\Order::where('coupon_discount','>',0)->whereBetween('created_at',[$start,$end])->sum('grand_total'),'campaigns'=>$campaigns];
        } catch (\Exception $e) { return ['campaign_visits'=>0,'coupon_revenue'=>0,'campaigns'=>[]]; }
    }

    public function getMarketingKpis(Carbon $start, Carbon $end): array
    {
        try {
            [$ps,$pe] = $this->safePeriod($start,$end);
            $couponRev = \App\Models\Order::where('coupon_discount','>',0)->whereBetween('created_at',[$start,$end])->sum('grand_total');
            $prevCouponRev = \App\Models\Order::where('coupon_discount','>',0)->whereBetween('created_at',[$ps,$pe])->sum('grand_total');
            $activeCoupons = \App\Models\Coupon::where('status',1)->where('end_date','>',now()->timestamp)->count();
            $totalRev = \App\Models\Order::where('payment_status','paid')->sum('grand_total');
            $buyers = \App\Models\Order::where('payment_status','paid')->distinct('user_id')->count('user_id');
            $ltv = $buyers>0?round((float)$totalRev/$buyers,2):0;
            return ['campaign_revenue'=>(float)$couponRev,'revenue_delta'=>$this->getDelta($couponRev,$prevCouponRev),'active_coupons'=>$activeCoupons,'customer_ltv'=>$ltv];
        } catch (\Exception $e) { return ['campaign_revenue'=>0,'revenue_delta'=>'0%','active_coupons'=>0,'customer_ltv'=>0]; }
    }

    public function getCouponTracker(): array
    {
        try {
            return \App\Models\Coupon::orderByDesc('created_at')->limit(5)->get()->map(function($c) {
                $uses = \App\Models\Order::where('coupon_code',$c->code)->count();
                $rev = \App\Models\Order::where('payment_status','paid')->where('coupon_code',$c->code)->sum('grand_total');
                return ['code'=>$c->code,'discount'=>$c->discount.($c->discount_type=='amount'?'':'%'),'uses'=>(int)$uses,'revenue'=>(float)$rev,'expires'=>Carbon::createFromTimestamp($c->end_date)->locale(app()->getLocale())->translatedFormat('d M Y')];
            })->toArray();
        } catch (\Exception $e) { return []; }
    }

    // ── SECURITY ──────────────────────────────────────────────────────────────

    public function getSecurityMetrics(Carbon $start, Carbon $end): array
    {
        $result = ['failed_logins'=>0,'blocked_uploads'=>0,'avg_latency'=>0,'recent_events'=>[]];
        try {
            if (!Schema::hasTable('audit_logs')) return $result;
            $result['failed_logins'] = DB::table('audit_logs')->where('action_type','FAILED_LOGIN')->whereBetween('created_at',[$start,$end])->count();
            $result['blocked_uploads'] = DB::table('audit_logs')->where('action_type','MALWARE_BLOCKED')->whereBetween('created_at',[$start,$end])->count();
            $result['recent_events'] = DB::table('audit_logs')->whereIn('action_type',['LOGIN','LOGOUT','FAILED_LOGIN','MALWARE_BLOCKED','UNAUTHORIZED_ACCESS'])
                ->whereBetween('created_at',[$start,$end])
                ->orderByDesc('created_at')->limit(10)->get()->map(function($e) {
                    $admin = null; try { $admin = \App\Models\User::find($e->admin_id ?? $e->user_id); } catch(\Exception $ex){}
                    return ['created_at'=>$e->created_at,'admin'=>['name'=>$admin?$admin->name:'System'],'action_type'=>$e->action_type??'','description'=>translate_security_event_description($e->description??''),'ip_address'=>$e->ip_address??''];
                })->toArray();
        } catch (\Exception $e) { Log::warning('[Analytics] Security: '.$e->getMessage()); }
        return $result;
    }

    // ── OPERATIONS ────────────────────────────────────────────────────────────

    public function getSystemHealth(): array
    {
        try {
            $dbLatency=0; $start=microtime(true);
            try { DB::connection()->getPdo(); $dbLatency=round((microtime(true)-$start)*1000); $dbOk=true; } catch(\Exception $e) { $dbOk=false; }
            return [
                ['name'=>'Core API','source'=>'System','rate'=>$dbOk?'100%':'0%','status'=>$dbOk?'ok':'error','latency'=>$dbLatency],
                ['name'=>'Database','source'=>'System','rate'=>$dbOk?'100%':'0%','status'=>$dbOk?'ok':'error','latency'=>$dbLatency],
            ];
        } catch (\Exception $e) { return []; }
    }

    public function getAutomatedInsights(): array
    {
        $insights = [];
        try {
            $recentOrders = \App\Models\Order::where('created_at','>=',now()->subHour())->count();
            if ($recentOrders > 10) $insights[] = ['level'=>'info','title'=>'High Order Volume','message'=>"$recentOrders ".translate('orders in the last hour.')];
            $pendingRefunds = \App\Models\RefundRequest::where('refund_status',0)->count();
            if ($pendingRefunds > 5) $insights[] = ['level'=>'warning','title'=>'Pending Refunds','message'=>"$pendingRefunds ".translate('refund requests awaiting review.')];
            $pendingPayouts = SellerWithdrawRequest::where('status',0)->count();
            if ($pendingPayouts > 3) $insights[] = ['level'=>'info','title'=>'Vendor Payouts Due','message'=>"$pendingPayouts ".translate('vendors awaiting payout.')];
        } catch (\Exception $e) {}
        return $insights;
    }

    public function getForecastingData(Carbon $start, Carbon $end): array
    {
        try {
            $history = \App\Models\Order::where('payment_status','paid')->whereBetween('created_at',[$start,$end])
                ->select(DB::raw('DATE(created_at) as date'),DB::raw('SUM(grand_total) as total'))
                ->groupBy('date')->orderBy('date')->get()->map(fn($r)=>['date'=>$r->date,'total'=>(float)$r->total])->toArray();
            $n=count($history); if($n<2) return ['history'=>$history,'forecast'=>[],'growth_rate'=>0];
            $sumX=$sumY=$sumXY=$sumXX=0;
            foreach($history as $i=>$r){$sumX+=$i;$sumY+=$r['total'];$sumXY+=($i*$r['total']);$sumXX+=($i*$i);}
            $denom=($n*$sumXX-$sumX*$sumX); $slope=$denom!=0?($n*$sumXY-$sumX*$sumY)/$denom:0; $intercept=($sumY-$slope*$sumX)/$n;
            $forecast=[]; $lastDate=Carbon::parse(end($history)['date']);
            for($i=1;$i<=7;$i++){$x=$n+$i-1;$forecast[]=['date'=>(clone $lastDate)->addDays($i)->format('Y-m-d'),'total'=>max(0,round($slope*$x+$intercept,2))];}
            return ['history'=>$history,'forecast'=>$forecast,'growth_rate'=>round($slope,2)];
        } catch (\Exception $e) { return ['history'=>[],'forecast'=>[],'growth_rate'=>0]; }
    }

    public function getCurrencyConfig(): array
    {
        try {
            $id = function_exists('get_setting') ? get_setting('system_default_currency') : null;
            if ($id) { $c=\App\Models\Currency::find($id); if($c) return ['symbol'=>$c->symbol,'code'=>$c->code]; }
        } catch (\Exception $e) {}
        return ['symbol'=>'$','code'=>'USD'];
    }

    private function approvedVendorQuery()
    {
        // Analytics counts approved seller accounts, not only shops currently
        // exposed by the storefront visibility policy (which intentionally
        // changes when the vendor marketplace is disabled).
        return \App\Models\Shop::query()
            ->where('approval_status', 'approved')
            ->whereHas('user', function ($user) {
                $user->where('user_type', 'seller')
                    ->where('banned', 0);
            });
    }
}

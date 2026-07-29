@php $cs = $currency['symbol'] ?? '$'; $vs = $visitorStats ?? []; $fs = $funnelStats ?? []; $fc = $forecasting ?? []; @endphp

{{-- OVERVIEW KPIs --}}
<div class="td-kpi-grid">
    @php $kpis = [
        ['label'=>'Total Revenue','value'=>$cs.number_format(array_sum(array_column($fc['history']??[],'total'))),'delta'=>($fc['growth_rate']??0)>0?'+'.($fc['growth_rate']??0).'%':($fc['growth_rate']??0).'%','icon'=>'💰','up'=>($fc['growth_rate']??0)>0],
        ['label'=>'Total Visits','value'=>number_format($vs['total_visits']??0),'delta'=>$vs['total_visits_delta']??'0%','icon'=>'👥','up'=>!str_contains($vs['total_visits_delta']??'','-')],
        ['label'=>'Avg Session','value'=>round($vs['avg_duration_sec']??0).'s','delta'=>$vs['avg_duration_delta']??'0%','icon'=>'⏱️','up'=>!str_contains($vs['avg_duration_delta']??'','-')],
        ['label'=>'Conversion Rate','value'=>(($vs['total_visits']??0)>0?round((($fs['purchased']??0)/($vs['total_visits']??1))*100,1):0).'%','delta'=>null,'icon'=>'🎯','up'=>true],
        ['label'=>'Bounce Rate','value'=>round($vs['bounce_rate']??0).'%','delta'=>$vs['bounce_rate_delta']??'0%','icon'=>'↩️','up'=>str_contains($vs['bounce_rate_delta']??'','-')],
    ]; @endphp
    @foreach($kpis as $k)
    <div class="td-kpi">
        <div class="td-kpi-icon">{{ $k['icon'] }}</div>
        <div class="td-kpi-label">{{ translate($k['label']) }}</div>
        <div class="td-kpi-value">{{ $k['value'] }}</div>
        @if($k['delta'])<span class="td-kpi-delta {{ $k['up']?'td-delta-up':'td-delta-down' }}">{{ $k['delta'] }}</span>@endif
    </div>
    @endforeach
</div>

{{-- REVENUE FORECAST + TRAFFIC PIE --}}
<div class="td-grid-sr" style="margin-bottom:16px">
    <div class="td-card">
        <div class="td-card-title">{{ translate('Revenue Analytics & Forecast') }}</div>
        <div class="td-card-sub">{{ translate('Historical sales with 7-day predictive modeling') }}</div>
        <div id="chart-forecast" style="min-height:300px"></div>
    </div>
    <div class="td-card">
        <div class="td-card-title">{{ translate('Traffic Composition') }}</div>
        <div class="td-card-sub">{{ translate('Session distribution by source') }}</div>
        <div id="chart-traffic" style="min-height:240px"></div>
    </div>
</div>

{{-- HOURLY + FUNNEL + GROWTH VELOCITY --}}
<div class="td-grid-3" style="margin-bottom:16px">
    <div class="td-card">
        <div class="td-card-title">{{ translate('Hourly Traffic') }}</div>
        <div class="td-card-sub">{{ translate('Visits per hour today') }}</div>
        <div id="chart-hourly" style="min-height:180px"></div>
    </div>
    <div class="td-card">
        <div class="td-card-title">{{ translate('Conversion Funnel') }}</div>
        <div class="td-card-sub">{{ translate('User journey breakdown') }}</div>
        @php $stages = [
            ['label'=>'Visits','val'=>$fs['visits']??0,'pct'=>100],
            ['label'=>'Product View','val'=>$fs['product_views']??0,'pct'=>($fs['visits']??0)>0?round((($fs['product_views']??0)/($fs['visits']??1))*100):0],
            ['label'=>'Add to Cart','val'=>$fs['add_to_cart']??0,'pct'=>($fs['visits']??0)>0?round((($fs['add_to_cart']??0)/($fs['visits']??1))*100):0],
            ['label'=>'Checkout','val'=>$fs['checkout']??0,'pct'=>($fs['visits']??0)>0?round((($fs['checkout']??0)/($fs['visits']??1))*100):0],
            ['label'=>'Purchased','val'=>$fs['purchased']??0,'pct'=>($fs['visits']??0)>0?round((($fs['purchased']??0)/($fs['visits']??1))*100):0],
        ]; @endphp
        @foreach($stages as $s)
        <div style="margin-bottom:8px">
            <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px">
                <span style="color:#64748b">{{ translate($s['label']) }}</span>
                <span style="font-weight:700;color:#0f172a">{{ number_format($s['val']) }} <span style="color:#94a3b8">({{ $s['pct'] }}%)</span></span>
            </div>
            <div class="td-funnel-bar"><div class="td-funnel-fill" style="width:{{ $s['pct'] }}%"></div></div>
        </div>
        @endforeach
    </div>
    <div class="td-card" style="text-align:center">
        <div class="td-card-title">{{ translate('Growth Velocity') }}</div>
        <div class="td-card-sub">{{ translate('Month-over-month trajectory') }}</div>
        <div style="font-size:48px;font-weight:800;color:{{ ($fc['growth_rate']??0)>0?'#10b981':'#ef4444' }};letter-spacing:-0.04em;margin:16px 0">
            {{ ($fc['growth_rate']??0)>0?'+':'' }}{{ $fc['growth_rate']??0 }}%
        </div>
        <div style="font-size:11px;color:#10b981;font-weight:700;letter-spacing:.08em">{{ translate(($fc['growth_rate']??0)>=18?'OUTPERFORMING':'GROWTH MOMENTUM') }}</div>
        <div class="td-grid-2" style="margin-top:12px;gap:8px">
            @foreach([['l'=>'Purchases','v'=>$fs['purchased']??0,'c'=>'#6366f1'],['l'=>'Bounce Exits','v'=>round(($vs['total_visits']??0)*(($vs['bounce_rate']??0)/100)),'c'=>'#f59e0b']] as $m)
            <div class="td-micro-kpi"><div class="label">{{ translate($m['l']) }}</div><div class="val" style="color:{{ $m['c'] }}">{{ number_format($m['v']) }}</div></div>
            @endforeach
        </div>
    </div>
</div>

{{-- TOP VENDORS + SYSTEM HEALTH --}}
<div class="td-grid-sl" style="margin-bottom:16px">
    <div class="td-card">
        <div class="td-card-title">{{ translate('Top Vendors') }}</div>
        <div class="td-card-sub">{{ translate('By revenue this period') }}</div>
        <table class="td-table">
            <thead><tr><th>{{ translate('Vendor') }}</th><th>{{ translate('Revenue') }}</th><th>{{ translate('Orders') }}</th><th>{{ translate('Rating') }}</th></tr></thead>
            <tbody>
            @forelse($vendorDirectory as $v)
            <tr>
                <td style="font-weight:600">{{ $v['seller']['shop']['name'] ?? 'N/A' }}</td>
                <td style="font-weight:700;color:#6366f1">{{ $cs }}{{ number_format($v['total_revenue']??0) }}</td>
                <td>{{ $v['total_orders']??0 }}</td>
                <td style="color:#f59e0b;font-weight:700">{{ $v['avg_rating']?'★'.$v['avg_rating']:'—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">{{ translate('No vendor data this period') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div>
        <div class="td-card">
            <div class="td-card-title">{{ translate('System Health') }}</div>
            <div class="td-card-sub">{{ translate('API & service status') }}</div>
            <table class="td-table">
                <thead><tr><th>{{ translate('Service') }}</th><th>{{ translate('Uptime') }}</th><th>{{ translate('Latency') }}</th><th>{{ translate('Status') }}</th></tr></thead>
                <tbody>
                @forelse($systemHealth as $s)
                <tr>
                    <td style="font-weight:600">{{ translate($s['name']) }}</td>
                    <td style="font-weight:700">{{ $s['rate'] }}</td>
                    <td style="color:#64748b">{{ $s['latency'] }}ms</td>
                    <td><span class="td-status-dot" style="background:{{ $s['status']==='ok'?'#10b981':'#f59e0b' }}"></span>{{ translate($s['status']==='ok'?'OK':'Warn') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:15px">{{ translate('Monitoring initializing...') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="td-card">
            <div class="td-card-title">{{ translate('Automated Insights') }}</div>
            <div class="td-card-sub">{{ translate('Performance signals') }}</div>
            @forelse($insights as $a)
            <div class="td-insight td-insight-{{ $a['level']??'info' }}">
                <span style="font-size:16px">{{ $a['level']==='critical'?'⚡':($a['level']==='warning'?'⚠️':'📈') }}</span>
                <div><div style="font-size:13px;font-weight:700;color:#0f172a">{{ translate($a['title']) }}</div><div style="font-size:11px;color:#64748b;margin-top:2px">{{ $a['message'] }}</div></div>
            </div>
            @empty
            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:12px">🧘 {{ translate('All systems nominal') }}</div>
            @endforelse
        </div>
    </div>
</div>

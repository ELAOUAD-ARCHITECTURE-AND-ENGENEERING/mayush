@php $cs = $currency['symbol'] ?? '$'; $mk = $marketingKpis ?? []; $mm = $marketingMetrics ?? []; @endphp

{{-- MARKETING KPIs --}}
<div class="td-kpi-grid">
    @php $kpis = [
        ['label'=>'Campaign Revenue','value'=>$cs.number_format($mk['campaign_revenue']??0),'delta'=>$mk['revenue_delta']??'0%','icon'=>'📣','up'=>!str_contains($mk['revenue_delta']??'','-')],
        ['label'=>'Active Coupons','value'=>$mk['active_coupons']??0,'delta'=>null,'icon'=>'🎫','up'=>true],
        ['label'=>'Customer LTV','value'=>$cs.number_format($mk['customer_ltv']??0),'delta'=>null,'icon'=>'👤','up'=>true],
    ]; @endphp
    @foreach($kpis as $k)
    <div class="td-kpi"><div class="td-kpi-icon">{{ $k['icon'] }}</div><div class="td-kpi-label">{{ $k['label'] }}</div><div class="td-kpi-value">{{ $k['value'] }}</div>@if($k['delta'])<span class="td-kpi-delta {{ $k['up']?'td-delta-up':'td-delta-down' }}">{{ $k['delta'] }}</span>@endif</div>
    @endforeach
</div>

{{-- CAMPAIGNS TABLE --}}
<div class="td-card">
    <div class="td-card-title">Campaign Performance</div>
    <div class="td-card-sub">Flash deals & promotions</div>
    <table class="td-table">
        <thead><tr><th>Campaign</th><th>Channel</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($mm['campaigns'] ?? [] as $c)
        <tr>
            <td style="font-weight:600">{{ $c['name']??'—' }}</td>
            <td><span class="td-badge td-badge-purple">{{ $c['channel']??'—' }}</span></td>
            <td><span class="td-badge {{ ($c['status']??'')==='Live'?'td-badge-ok':'td-badge-warn' }}">{{ $c['status']??'—' }}</span></td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:15px">No campaigns</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- COUPON TRACKER + INSIGHTS --}}
<div class="td-grid-2">
    <div class="td-card">
        <div class="td-card-title">Coupon Tracker</div>
        <div class="td-card-sub">Discount impact logs</div>
        <table class="td-table">
            <thead><tr><th>Code</th><th>Discount</th><th>Uses</th><th>Revenue</th><th>Expires</th></tr></thead>
            <tbody>
            @forelse($couponTracker as $c)
            <tr>
                <td style="font-weight:700;color:#6366f1;font-family:monospace">{{ $c['code'] }}</td>
                <td style="font-weight:800;color:#10b981">{{ $c['discount'] }}</td>
                <td>{{ $c['uses'] }}</td>
                <td style="font-weight:700">{{ $cs }}{{ number_format($c['revenue']??0) }}</td>
                <td style="font-size:10px;color:#64748b">{{ $c['expires'] }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:15px">No coupons</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="td-card">
        <div class="td-card-title">Marketing Insights</div>
        <div class="td-card-sub">Growth recommendations</div>
        @foreach([
            ['t'=>'Best Send Time','m'=>'Thursdays at 6:45 PM for highest CTR','i'=>'⏰','c'=>'#6366f1'],
            ['t'=>'Retargeting ROI','m'=>'Abandoned cart emails up 400%','i'=>'♻️','c'=>'#10b981'],
            ['t'=>'Budget Advice','m'=>'Shift 12% from Search to Social','i'=>'💡','c'=>'#f59e0b'],
            ['t'=>'Cohort Strategy','m'=>'Jan cohort shows peak LTV at M4','i'=>'📊','c'=>'#8b5cf6'],
        ] as $s)
        <div style="display:flex;gap:12px;padding:12px;background:#fafafa;border-radius:12px;border-left:3px solid {{ $s['c'] }};margin-bottom:8px">
            <span style="font-size:18px">{{ $s['i'] }}</span>
            <div><div style="font-size:12px;font-weight:700;color:#0f172a">{{ $s['t'] }}</div><div style="font-size:11px;color:#64748b;margin-top:2px">{{ $s['m'] }}</div></div>
        </div>
        @endforeach
    </div>
</div>

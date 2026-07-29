@php $cs = $currency['symbol'] ?? '$'; $fk = $financeKpis ?? []; $pp = $profitability ?? []; @endphp

{{-- FINANCE KPIs --}}
<div class="td-kpi-grid">
    @php $kpis = [
        ['label'=>'Gross GMV','value'=>$cs.number_format($fk['gross_gmv']??0),'delta'=>$fk['gross_gmv_delta']??'0%','icon'=>'📊','up'=>!str_contains($fk['gross_gmv_delta']??'','-')],
        ['label'=>'Net Revenue','value'=>$cs.number_format($fk['net_revenue']??0),'delta'=>$fk['net_revenue_delta']??'0%','icon'=>'💰','up'=>!str_contains($fk['net_revenue_delta']??'','-')],
        ['label'=>'Commission','value'=>$cs.number_format($fk['commission']??0),'delta'=>$fk['commission_delta']??'0%','icon'=>'💎','up'=>!str_contains($fk['commission_delta']??'','-')],
        ['label'=>'Refund Rate','value'=>($fk['refund_rate']??0).'%','delta'=>$fk['refund_delta']??'0%','icon'=>'↩️','up'=>str_contains($fk['refund_delta']??'','-')],
        ['label'=>'Pending Payouts','value'=>$cs.number_format($fk['pending_payouts']??0),'delta'=>($fk['pending_vendors']??0).' vendors','icon'=>'⏳','up'=>false],
    ]; @endphp
    @foreach($kpis as $k)
    <div class="td-kpi"><div class="td-kpi-icon">{{ $k['icon'] }}</div><div class="td-kpi-label">{{ translate($k['label']) }}</div><div class="td-kpi-value">{{ $k['value'] }}</div>@if($k['delta'])<span class="td-kpi-delta {{ $k['up']?'td-delta-up':'td-delta-down' }}">{{ $k['delta'] }}</span>@endif</div>
    @endforeach
</div>

{{-- EARNINGS CHART + PROFITABILITY --}}
<div class="td-grid-2" style="margin-bottom:16px">
    <div class="td-card"><div class="td-card-title">{{ translate('Earnings & Outflows') }}</div><div class="td-card-sub">{{ translate('Commission vs Refunds') }}</div><div id="chart-finance" style="min-height:260px"></div></div>
    <div class="td-card">
        <div class="td-card-title">{{ translate('Profitability Pulse') }}</div>
        <div class="td-card-sub">{{ translate('Transaction health metrics') }}</div>
        <div style="margin-bottom:16px"><div style="font-size:11px;color:#94a3b8;text-transform:uppercase;margin-bottom:4px">{{ translate('Refund Trend') }}</div><div id="chart-refund" style="min-height:120px"></div></div>
        <div class="td-grid-2" style="gap:8px">
            <div class="td-micro-kpi"><div class="label">{{ translate('Avg Order Val') }}</div><div class="val">{{ $cs }}{{ number_format($pp['aov']??0,2) }}</div></div>
            <div class="td-micro-kpi"><div class="label">{{ translate('Items/Order') }}</div><div class="val" style="color:#10b981">{{ $pp['items_per_order']??0 }}</div></div>
        </div>
    </div>
</div>

{{-- PAYOUTS + TAX --}}
<div class="td-grid-sl">
    <div class="td-card">
        <div class="td-card-title">{{ translate('Vendor Payouts') }}</div>
        <div class="td-card-sub">{{ translate('Awaiting reconciliation') }}</div>
        <table class="td-table">
            <thead><tr><th>{{ translate('Vendor') }}</th><th>{{ translate('Amount') }}</th><th>{{ translate('Status') }}</th><th>{{ translate('Date') }}</th></tr></thead>
            <tbody>
            @forelse($payouts as $p)
            <tr>
                <td style="font-weight:600">{{ $p['vendor'] }}</td>
                <td style="font-weight:700;color:#6366f1">{{ $cs }}{{ number_format($p['amount']??0) }}</td>
                <td><span class="td-badge {{ $p['status']==='Paid'?'td-badge-ok':($p['status']==='Pending'?'td-badge-warn':'td-badge-info') }}">{{ translate($p['status']) }}</span></td>
                <td style="color:#64748b">{{ $p['date'] }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:15px">{{ translate('No payouts') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="td-card">
        <div class="td-card-title">{{ translate('Tax Collection') }}</div>
        <div class="td-card-sub">{{ translate('Regional compliance') }}</div>
        <table class="td-table">
            <thead><tr><th>{{ translate('Region') }}</th><th>{{ translate('Collected') }}</th><th>{{ translate('Rate') }}</th><th>{{ translate('Status') }}</th></tr></thead>
            <tbody>
            @forelse($taxCollection as $t)
            <tr>
                <td>{{ $t['region'] }}</td>
                <td style="font-weight:700">{{ $cs }}{{ number_format($t['collected']??0) }}</td>
                <td style="color:#64748b">{{ $t['rate'] }}</td>
                <td><span class="td-badge td-badge-ok">{{ translate($t['status']) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:15px">{{ translate('No tax data') }}</td></tr>
            @endforelse
            </tbody>
        </table>
        @if(count($taxCollection)>0)
        <div style="margin-top:12px;padding:10px;background:#f0fdf4;border-radius:8px;text-align:center;border:1px solid #dcfce7">
            <span style="font-size:11px;color:#64748b">{{ translate('Total Tax') }}:</span>
            <span style="font-size:14px;font-weight:800;color:#16a34a">{{ $cs }}{{ number_format(array_sum(array_column($taxCollection,'collected'))) }}</span>
        </div>
        @endif
    </div>
</div>

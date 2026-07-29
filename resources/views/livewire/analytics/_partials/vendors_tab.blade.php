@php $cs = $currency['symbol'] ?? '$'; $vk = $vendorKpis ?? []; @endphp

{{-- VENDOR KPIs --}}
<div class="td-kpi-grid">
    @php $kpis = [
        ['label'=>'Active Vendors','value'=>$vk['active']??0,'icon'=>'🏪','delta'=>null],
        ['label'=>'New This Month','value'=>$vk['new']??0,'icon'=>'✨','delta'=>null],
        ['label'=>'Avg Rating','value'=>($vk['rating']??0).'★','icon'=>'⭐','delta'=>null],
        ['label'=>'Total GMV','value'=>$cs.number_format($vk['gmv']??0),'icon'=>'💰','delta'=>null],
        ['label'=>'Dispute Rate','value'=>($vk['dispute_rate']??0).'%','icon'=>'⚖️','delta'=>null],
    ]; @endphp
    @foreach($kpis as $k)
    <div class="td-kpi"><div class="td-kpi-icon">{{ $k['icon'] }}</div><div class="td-kpi-label">{{ translate($k['label']) }}</div><div class="td-kpi-value">{{ $k['value'] }}</div></div>
    @endforeach
</div>

{{-- GROWTH CHART + CATEGORY PIE --}}
<div class="td-grid-2" style="margin-bottom:16px">
    <div class="td-card"><div class="td-card-title">{{ translate('Vendor Growth') }}</div><div class="td-card-sub">{{ translate('Monthly acquisition & retention') }}</div><div id="chart-vendor-growth" style="min-height:240px"></div></div>
    <div class="td-card"><div class="td-card-title">{{ translate('Sales by Category') }}</div><div class="td-card-sub">{{ translate('Revenue distribution') }}</div><div id="chart-categories" style="min-height:240px"></div></div>
</div>

{{-- VENDOR DIRECTORY --}}
<div class="td-card">
    <div class="td-card-title">{{ translate('Vendor Directory') }}</div>
    <div class="td-card-sub">{{ translate('Real-time performance ranking') }}</div>
    <table class="td-table">
        <thead><tr><th>{{ translate('Vendor') }}</th><th>{{ translate('Revenue') }}</th><th>{{ translate('Orders') }}</th><th>{{ translate('Rating') }}</th></tr></thead>
        <tbody>
        @forelse($vendorDirectory as $v)
        <tr>
            <td><div style="display:flex;align-items:center;gap:8px"><div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800">{{ strtoupper(substr($v['seller']['shop']['name']??'?',0,1)) }}</div><div><div style="font-weight:700">{{ $v['seller']['shop']['name']??'N/A' }}</div></div></div></td>
            <td style="font-weight:700;color:#6366f1">{{ $cs }}{{ number_format($v['total_revenue']??0) }}</td>
            <td>{{ $v['total_orders']??0 }}</td>
            <td style="color:#f59e0b;font-weight:700">{{ $v['avg_rating']?'★'.$v['avg_rating']:'—' }}</td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px">{{ translate('No vendor data') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

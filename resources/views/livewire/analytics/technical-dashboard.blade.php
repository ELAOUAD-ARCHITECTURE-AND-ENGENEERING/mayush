<div class="td-wrap" wire:loading.class="td-loading">
    @php $cs = $currency['symbol'] ?? '$'; @endphp

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- HEADER --}}
    <div class="td-header">
        <div>
            <div class="td-title">⚡ {{ translate('MarketOps Dashboard') }}</div>
            <div class="td-subtitle"><span class="td-live-dot"></span> {{ translate('System Live') }} · {{ translate('Updated just now') }}</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <div class="td-date-wrap">
                @foreach(['Today','7D','30D','90D'] as $d)
                <button wire:click="setDateRange('{{ $d }}')" class="td-date-btn {{ $dateRange === $d ? 'active' : '' }}">{{ translate($d) }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TABS --}}
    <div class="td-tabs" style="margin-bottom:24px">
        @foreach(['Overview','Vendors','Finance','Marketing','Security'] as $tab)
        <button wire:click="setActiveTab('{{ $tab }}')" class="td-tab {{ $activeTab === $tab ? 'active' : '' }}">{{ translate($tab) }}</button>
        @endforeach
    </div>

    {{-- TAB CONTENT --}}
    <div class="td-fade" wire:key="tab-{{ $activeTab }}">
        @if($activeTab === 'Overview')
            @include('livewire.analytics._partials.overview_tab')
        @elseif($activeTab === 'Vendors')
            @include('livewire.analytics._partials.vendors_tab')
        @elseif($activeTab === 'Finance')
            @include('livewire.analytics._partials.finance_tab')
        @elseif($activeTab === 'Marketing')
            @include('livewire.analytics._partials.marketing_tab')
        @elseif($activeTab === 'Security')
            @include('livewire.analytics._partials.security_tab')
        @endif
    </div>

    {{-- STYLES MOVED TO BOTTOM FOR MORPH RESILIENCE --}}
    <style wire:ignore>
        .td-wrap{padding:20px;font-family:'Inter',system-ui,sans-serif;color:#1e293b;min-height:100vh;background:#f8fafc;position:relative}
        .td-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px}
        .td-title{font-size:22px;font-weight:800;letter-spacing:-0.03em;color:#0f172a}
        .td-subtitle{font-size:12px;color:#64748b;margin-top:4px;display:flex;align-items:center;gap:6px}
        .td-live-dot{width:7px;height:7px;border-radius:50%;background:#10b981;display:inline-block;animation:td-pulse 2s infinite}
        @keyframes td-pulse{0%,100%{opacity:1}50%{opacity:.3}}
        @keyframes td-fadein{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .td-fade{animation:td-fadein .4s ease both}
        .td-tabs{display:flex;gap:4px;background:#f1f5f9;padding:4px;border-radius:12px;border:1px solid #e2e8f0;flex-wrap:wrap}
        .td-tab{padding:7px 16px;border-radius:9px;font-size:12px;font-weight:700;border:none;cursor:pointer;background:transparent;color:#64748b;transition:all .2s;z-index:10}
        .td-tab.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 12px rgba(99,102,241,.25)}
        .td-tab:hover:not(.active){background:rgba(99,102,241,.08)}
        .td-date-wrap{display:flex;gap:4px;background:#f1f5f9;padding:4px;border-radius:10px;border:1px solid #e2e8f0}
        .td-date-btn{padding:5px 12px;border-radius:7px;font-size:11px;font-weight:700;border:none;cursor:pointer;background:transparent;color:#64748b;transition:all .2s}
        .td-date-btn.active{background:rgba(99,102,241,.25);color:#6366f1}
        .td-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px}
        .td-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px 20px;position:relative;overflow:hidden;transition:box-shadow .2s}
        .td-kpi:hover{box-shadow:0 8px 25px rgba(0,0,0,.06)}
        .td-kpi-icon{position:absolute;top:14px;right:14px;font-size:20px;opacity:.6}
        .td-kpi-label{font-size:10px;font-weight:700;letter-spacing:.1em;color:#6366f1;text-transform:uppercase}
        .td-kpi-value{font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.03em;margin-top:4px}
        .td-kpi-delta{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;margin-top:6px}
        .td-delta-up{background:rgba(16,185,129,.1);color:#10b981}
        .td-delta-down{background:rgba(239,68,68,.1);color:#ef4444}
        .td-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 22px;margin-bottom:16px;transition:box-shadow .2s}
        .td-card:hover{box-shadow:0 8px 25px rgba(0,0,0,.05)}
        .td-card-title{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:2px}
        .td-card-sub{font-size:11px;color:#94a3b8;margin-bottom:16px}
        .td-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .td-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
        .td-grid-sr{display:grid;grid-template-columns:2fr 1fr;gap:16px}
        .td-grid-sl{display:grid;grid-template-columns:1.2fr .8fr;gap:16px}
        @media(max-width:1200px){.td-grid-2,.td-grid-3,.td-grid-sr,.td-grid-sl{grid-template-columns:1fr}}
        .td-table{width:100%;border-collapse:collapse}
        .td-table th{font-size:10px;font-weight:700;color:#475569;text-align:left;padding:8px 10px;text-transform:uppercase;letter-spacing:.06em;border-bottom:2px solid #f1f5f9}
        .td-table td{padding:10px;font-size:12px;color:#334155;border-bottom:1px solid #f8fafc}
        .td-table tr:hover{background:#f8fafc}
        .td-badge{padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;display:inline-block}
        .td-badge-ok{background:#dcfce7;color:#16a34a}.td-badge-warn{background:#fef3c7;color:#d97706}.td-badge-err{background:#fee2e2;color:#dc2626}
        .td-badge-info{background:#dbeafe;color:#2563eb}.td-badge-purple{background:#ede9fe;color:#7c3aed}
        .td-status-dot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:5px}
        .td-funnel-bar{height:8px;border-radius:4px;background:#f1f5f9;overflow:hidden;margin-top:4px}
        .td-funnel-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#6366f1,#8b5cf6);transition:width .5s}
        .td-insight{padding:12px 16px;border-radius:12px;border-left:3px solid;display:flex;gap:10px;align-items:flex-start;margin-bottom:8px;background:#fafafa}
        .td-insight-critical{border-color:#ef4444}.td-insight-warning{border-color:#f59e0b}.td-insight-info{border-color:#6366f1}
        .td-loading{opacity:.7;transition:opacity .3s}
        .td-export-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:9px;color:#fff;padding:7px 16px;font-size:12px;font-weight:700;cursor:pointer;letter-spacing:.02em}
        .td-micro-kpi{background:#f8fafc;border:1px solid #f1f5f9;border-radius:10px;padding:10px 12px;text-align:center}
        .td-micro-kpi .label{font-size:9px;color:#94a3b8;text-transform:uppercase}.td-micro-kpi .val{font-size:16px;font-weight:800;color:#6366f1}
    </style>

    {{-- APEXCHARTS --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>
    
    <script>
    function safeChart(id,opts){
        var el=document.querySelector('#'+id);
        if(!el)return;
        el.innerHTML='';
        try{new ApexCharts(el,opts).render();}catch(e){console.warn('Chart error:',e);}
    }
    
    function initCharts(){
        console.log('Initializing charts...');
        var hist = {!! json_encode($forecasting['history'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        var fore = {!! json_encode($forecasting['forecast'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        var forecastEl = document.querySelector('#chart-forecast');
        if(forecastEl) {
            if(hist.length>0){
                safeChart('chart-forecast',{chart:{type:'area',height:300,toolbar:{show:false}},series:[{name:'{!! addslashes(translate('Revenue')) !!}',data:hist.map(h=>h.total)},{name:'{!! addslashes(translate('Forecast')) !!}',data:hist.map(()=>null).concat(fore.map(f=>f.total))}],xaxis:{categories:hist.map(h=>h.date).concat(fore.map(f=>f.date)),labels:{show:false}},colors:['#6366f1','#a78bfa'],stroke:{width:[3,2],dashArray:[0,5]},fill:{type:['gradient','gradient'],gradient:{shadeIntensity:1,opacityFrom:.4,opacityTo:.05}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});
            } else {
                forecastEl.innerHTML = '<div style="display:flex;height:100%;align-items:center;justify-content:center;color:#94a3b8;font-size:12px">{!! addslashes(translate('No revenue data available for this period')) !!}</div>';
            }
        }
        
        var hourly = {!! json_encode($hourlyTraffic ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        if(hourly.length>0){safeChart('chart-hourly',{chart:{type:'bar',height:180,toolbar:{show:false}},series:[{name:'{!! addslashes(translate('Visits')) !!}',data:hourly.map(h=>h.v)}],xaxis:{categories:hourly.map(h=>h.h)},colors:['#6366f1'],plotOptions:{bar:{borderRadius:4}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});}
        
        var traf = {!! json_encode($trafficComposition ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        if(traf.length>0){safeChart('chart-traffic',{chart:{type:'donut',height:240},series:traf.map(t=>t.count),labels:traf.map(t=>t.source),colors:['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981'],legend:{position:'bottom',fontSize:'11px'},tooltip:{theme:'dark'}});}
        
        var fc = {!! json_encode($financeChart ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        if(fc.map && fc.length>0){safeChart('chart-finance',{chart:{type:'bar',height:260,toolbar:{show:false}},series:[{name:'{!! addslashes(translate('Commission')) !!}',data:fc.map(f=>f.commission)},{name:'{!! addslashes(translate('Refunds')) !!}',data:fc.map(f=>f.refunds)}],xaxis:{categories:fc.map(f=>f.month)},colors:['#6366f1','#f87171'],plotOptions:{bar:{borderRadius:4}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});}
        
        var rt = {!! json_encode($refundTrend ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        if(rt.map && rt.length>0){safeChart('chart-refund',{chart:{type:'area',height:120,toolbar:{show:false},sparkline:{enabled:true}},series:[{name:'{!! addslashes(translate('Refund %')) !!}',data:rt.map(r=>r.value)}],colors:['#f87171'],fill:{type:'gradient',gradient:{opacityFrom:.4,opacityTo:.05}},tooltip:{theme:'dark'}});}
        
        var vg = {!! json_encode($vendorGrowth ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        if(vg.map && vg.length>0){safeChart('chart-vendor-growth',{chart:{type:'bar',height:240,toolbar:{show:false}},series:[{name:'{!! addslashes(translate('Active')) !!}',data:vg.map(v=>v.active)},{name:'{!! addslashes(translate('New')) !!}',data:vg.map(v=>v.new)}],xaxis:{categories:vg.map(v=>v.month)},colors:['#6366f1','#10b981'],plotOptions:{bar:{borderRadius:4}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});}
        
        var cd = {!! json_encode($categoryDistribution ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        if(cd.map && cd.length>0){safeChart('chart-categories',{chart:{type:'donut',height:240},series:cd.map(c=>c.value),labels:cd.map(c=>c.name),colors:['#6366f1','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4'],legend:{position:'bottom',fontSize:'11px'},tooltip:{theme:'dark'}});}
    }
    
    document.addEventListener('livewire:init', () => {
        initCharts();
        Livewire.hook('morph.updated', ({ el, component }) => {
            if (component.name === 'analytics.technical-dashboard') {
                setTimeout(initCharts, 50);
            }
        });
    });

    // Fallback
    window.onload = function() {
        setTimeout(initCharts, 500);
    };
    </script>
</div>

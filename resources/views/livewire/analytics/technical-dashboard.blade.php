@assets
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endassets

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto bg-slate-900 min-h-screen text-slate-200">
    <style>
        .kpi-card { background: linear-gradient(135deg, #1e1e2e 0%, #16213e 100%); border: 1px solid rgba(99,102,241,0.18); border-radius: 16px; padding: 20px 22px; position: relative; overflow: hidden; }
        .kpi-title { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; color: #6366f1; text-transform: uppercase; }
        .kpi-value { font-size: 30px; font-weight: 800; color: #f8fafc; letter-spacing: -0.03em; }
        .section-card { background: linear-gradient(135deg, #1e1e2e 0%, #16213e 100%); border: 1px solid rgba(99,102,241,0.14); border-radius: 18px; padding: 22px 24px; }
        .tab-btn { padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; transition: all 0.2s; }
        .tab-btn.active { background: rgba(99,102,241,0.2); color: #818cf8; border: 1px solid #6366f1; }
        .tab-btn.inactive { color: #64748b; border: 1px solid transparent; }
        .tab-btn.inactive:hover { background: rgba(255,255,255,0.05); color: #e2e8f0; }
    </style>

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-white font-bold tracking-tight">Operations Dashboard</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs text-slate-400">System Live · Real-time streaming · Updated just now</span>
            </div>
        </div>
        
        <div class="flex gap-4 items-center">
            <!-- Tabs -->
            <div class="flex bg-slate-800/50 p-1 rounded-xl border border-slate-700/50">
                <button wire:click="setActiveTab('Overview')" class="tab-btn {{ $activeTab === 'Overview' ? 'active' : 'inactive' }}">Overview</button>
                <button wire:click="setActiveTab('Vendors')" class="tab-btn {{ $activeTab === 'Vendors' ? 'active' : 'inactive' }}">Vendors</button>
                <button wire:click="setActiveTab('Finance')" class="tab-btn {{ $activeTab === 'Finance' ? 'active' : 'inactive' }}">Finance</button>
            </div>

            <!-- Date Range Filter -->
            <div class="flex bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-1">
                @foreach(['Today', '7D', '30D', '90D'] as $range)
                    <button wire:click="setDateRange('{{ $range }}')" class="px-3 py-1 text-xs font-bold rounded-md transition-colors {{ $dateRange === $range ? 'bg-indigo-500/30 text-indigo-400' : 'text-slate-500 hover:text-slate-300' }}">
                        {{ $range }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- KPIs (Always visible, changes based on tab) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="kpi-card">
            <div class="absolute top-4 right-4 text-2xl opacity-70">💰</div>
            <div class="kpi-title">Gross GMV</div>
            <div class="kpi-value">${{ number_format($financeKpis['grossGmv'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ str_starts_with($financeKpis['grossGmvDelta'] ?? '', '+') ? 'bg-emerald-400/10 text-emerald-400' : 'bg-rose-400/10 text-rose-400' }}">
                    {{ $financeKpis['grossGmvDelta'] ?? '0%' }}
                </span>
                <span class="text-[10px] text-slate-500">vs prev period</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="absolute top-4 right-4 text-2xl opacity-70">💎</div>
            <div class="kpi-title">Net Revenue</div>
            <div class="kpi-value">${{ number_format($financeKpis['netRevenue'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ str_starts_with($financeKpis['netRevenueDelta'] ?? '', '+') ? 'bg-emerald-400/10 text-emerald-400' : 'bg-rose-400/10 text-rose-400' }}">
                    {{ $financeKpis['netRevenueDelta'] ?? '0%' }}
                </span>
                <span class="text-[10px] text-slate-500">platform edge</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="absolute top-4 right-4 text-2xl opacity-70">🏦</div>
            <div class="kpi-title">Commission</div>
            <div class="kpi-value">${{ number_format($financeKpis['commission'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ str_starts_with($financeKpis['commissionDelta'] ?? '', '+') ? 'bg-emerald-400/10 text-emerald-400' : 'bg-rose-400/10 text-rose-400' }}">
                    {{ $financeKpis['commissionDelta'] ?? '0%' }}
                </span>
                <span class="text-[10px] text-slate-500">admin earnings</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="absolute top-4 right-4 text-2xl opacity-70">↩️</div>
            <div class="kpi-title">Refund Rate</div>
            <div class="kpi-value">{{ number_format($financeKpis['refundRate'] ?? 0, 2) }}%</div>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ str_starts_with($financeKpis['refundDelta'] ?? '', '-') ? 'bg-emerald-400/10 text-emerald-400' : 'bg-rose-400/10 text-rose-400' }}">
                    {{ $financeKpis['refundDelta'] ?? '0%' }}
                </span>
                <span class="text-[10px] text-slate-500">quality score</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="absolute top-4 right-4 text-2xl opacity-70">⏳</div>
            <div class="kpi-title">Pending Payouts</div>
            <div class="kpi-value">${{ number_format($financeKpis['pendingPayouts'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="text-[10px] text-slate-500">{{ $financeKpis['pendingVendors'] ?? 0 }} vendors awaiting action</span>
            </div>
        </div>
    </div>

    <!-- Dynamic Tab Content -->
    @if($activeTab === 'Finance')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Payouts Table -->
            <div class="lg:col-span-2 section-card">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="text-[15px] font-bold text-slate-100 tracking-tight">Recent Vendor Payouts</div>
                        <div class="text-[11px] text-slate-400 mt-1">Awaiting reconciliation</div>
                    </div>
                    <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-[11px] font-bold py-1.5 px-3 rounded-md transition-colors">Run Payouts</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="text-[10px] font-bold text-slate-500 uppercase tracking-wider pb-3 border-b border-indigo-500/20">Vendor</th>
                                <th class="text-[10px] font-bold text-slate-500 uppercase tracking-wider pb-3 border-b border-indigo-500/20">Amount</th>
                                <th class="text-[10px] font-bold text-slate-500 uppercase tracking-wider pb-3 border-b border-indigo-500/20">Status</th>
                                <th class="text-[10px] font-bold text-slate-500 uppercase tracking-wider pb-3 border-b border-indigo-500/20">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr class="border-b border-slate-700/30 hover:bg-white/5 transition-colors">
                                <td class="py-3 text-xs font-semibold text-slate-200">{{ $payout['vendor'] }}</td>
                                <td class="py-3 text-xs font-bold text-indigo-400">${{ number_format($payout['amount'], 2) }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 rounded text-[10px] font-bold {{ $payout['status'] === 'Paid' ? 'bg-emerald-500/10 text-emerald-500' : ($payout['status'] === 'Pending' ? 'bg-amber-500/10 text-amber-500' : 'bg-indigo-500/10 text-indigo-400') }}">
                                        {{ $payout['status'] }}
                                    </span>
                                </td>
                                <td class="py-3 text-[11px] text-slate-400">{{ $payout['date'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-xs text-slate-500">No recent payouts found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Refund Trend Chart Placeholder -->
            <div class="section-card flex flex-col">
                <div class="mb-4">
                    <div class="text-[15px] font-bold text-slate-100 tracking-tight">Refund Trend</div>
                    <div class="text-[11px] text-slate-400 mt-1">Quality score timeline</div>
                </div>
                <div class="flex-grow flex items-center justify-center bg-slate-800/30 rounded-lg border border-slate-700/50" 
                     x-data="refundChartData(@js($refundTrend))"
                >
                    <div x-ref="refundChart" class="w-full h-full"></div>
                </div>
            </div>
        </div>
    @elseif($activeTab === 'Overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
             <div class="lg:col-span-2 section-card flex flex-col h-80">
                <div class="mb-4">
                    <div class="text-[15px] font-bold text-slate-100 tracking-tight">Revenue Analytics & Forecast</div>
                    <div class="text-[11px] text-slate-400 mt-1">Historical sales with predictive modeling</div>
                </div>
                <div class="flex-grow flex items-center justify-center bg-slate-800/30 rounded-lg border border-slate-700/50">
                    <div class="text-sm text-slate-500">[ApexCharts AreaChart]</div>
                </div>
            </div>
            <div class="section-card flex flex-col h-80">
                <div class="mb-4">
                    <div class="text-[15px] font-bold text-slate-100 tracking-tight">Traffic Composition</div>
                    <div class="text-[11px] text-slate-400 mt-1">Session distribution by source</div>
                </div>
                <div class="flex-grow flex items-center justify-center bg-slate-800/30 rounded-lg border border-slate-700/50">
                    <div class="text-sm text-slate-500">[ApexCharts PieChart]</div>
                </div>
            </div>
        </div>
    @else
        <div class="section-card">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <div class="text-[15px] font-bold text-slate-100 tracking-tight">Vendor Directory</div>
                    <div class="text-[11px] text-slate-400 mt-1">Real-time performance ranking</div>
                </div>
            </div>
            <div class="flex items-center justify-center py-20 text-slate-500 text-sm border border-dashed border-slate-700 rounded-lg">
                [Vendor Table Implementation Pending]
            </div>
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('refundChartData', (trendData) => ({
        trend: trendData,
        init() {
            if (this.$refs.refundChart.innerHTML !== '') {
                this.$refs.refundChart.innerHTML = '';
            }
            let options = {
                series: [{ name: 'Refund Rate', data: this.trend.map(t => t.value) }],
                chart: { type: 'area', height: 200, toolbar: { show: false }, background: 'transparent' },
                colors: ['#f87171'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0, stops: [0, 90, 100] } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: this.trend.map(t => t.date), labels: { style: { colors: '#64748b' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { colors: '#64748b' } } },
                grid: { borderColor: 'rgba(255,255,255,0.05)', strokeDashArray: 4 },
                theme: { mode: 'dark' }
            };
            let chart = new ApexCharts(this.$refs.refundChart, options);
            chart.render();
        }
    }));
</script>
@endscript

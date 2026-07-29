@extends('seller.layouts.app')

@section('panel_content')
    <div class="row gutters-10">
        <!-- Revenue Cards -->
        <div class="col-md-3">
            <div class="card mb-3 analytic-card primary">
                <div class="card-body">
                    <div class="text-white-50 fs-12 fw-600 text-uppercase d-block mb-1">{{ translate('Gross Revenue') }}</div>
                    <div class="d-flex align-items-center">
                        <span class="h3 fw-800 text-white mb-0" id="fin-gross">--</span>
                        <span class="ml-2 badge badge-inline badge-soft-light d-none" id="gross-trend">+0%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3 analytic-card success">
                <div class="card-body">
                    <div class="text-white-50 fs-12 fw-600 text-uppercase d-block mb-1">{{ translate('Net Earnings') }}</div>
                    <div class="h3 fw-800 text-white mb-0" id="fin-net">--</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3 analytic-card info">
                <div class="card-body">
                    <div class="text-white-50 fs-12 fw-600 text-uppercase d-block mb-1">{{ translate('Total Orders') }}</div>
                    <div class="h3 fw-800 text-white mb-0" id="fin-orders">--</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-3 analytic-card warning">
                <div class="card-body">
                    <div class="text-white-50 fs-12 fw-600 text-uppercase d-block mb-1">{{ translate('Payout Ready') }}</div>
                    <div class="h3 fw-800 text-white mb-0" id="fin-payout">--</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Picker -->
    <div class="row align-items-center mb-4">
        <div class="col-md-4">
            <input type="text" class="form-control aiz-date-range" id="date-range" name="date" placeholder="{{ translate('Filter by date range') }}">
        </div>
    </div>

    <div class="row">
        <!-- Revenue Trend Chart -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Revenue Trend') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenue-chart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Conversion Funnel -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Conversion Funnel') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="funnel-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Products -->
        <div class="col-md-7">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <h6 class="mb-0 fw-700 text-dark">{{ translate('Top Performing Products') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table aiz-table mb-0">
                            <thead>
                                <tr class="text-muted fs-12 text-uppercase">
                                    <th>{{ translate('Product') }}</th>
                                    <th class="text-right">{{ translate('Views') }}</th>
                                    <th class="text-right">{{ translate('Adds') }}</th>
                                    <th class="text-right">{{ translate('Sold') }}</th>
                                    <th class="text-right">{{ translate('Conv %') }}</th>
                                </tr>
                            </thead>
                            <tbody id="top-products-tbody" class="fs-14">
                                <!-- Populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Geo Analytics -->
        <div class="col-md-5">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <h6 class="mb-0 fw-700 text-dark">{{ translate('Regional Sales') }}</h6>
                </div>
                <div class="card-body">
                    <div id="geo-stats-container">
                        <div class="text-center py-4 text-muted">{{ translate('Loading regional data...') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projected Earnings Banner -->
    <div class="alert alert-soft-primary border-0 rounded-lg p-4 d-flex align-items-center mb-4" id="projected-banner">
        <div class="mr-3">
            <i class="las la-chart-bar la-3x text-primary"></i>
        </div>
        <div>
            <h5 class="fw-700 mb-1">{{ translate('Growth Projection') }}</h5>
            <p class="mb-0 text-secondary">
                {{ translate('You have approximately') }} <span class="fw-800 text-primary" id="proj-amount">--</span> 
                {{ translate('in projected net earnings from processing orders.') }}
            </p>
        </div>
    </div>

    <style>
        .analytic-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .analytic-card:hover {
            transform: translateY(-5px);
        }
        .analytic-card.primary { background: linear-gradient(135deg, #e2413e, #ff6b6b); box-shadow: 0 10px 20px rgba(226, 65, 62, 0.2); }
        .analytic-card.success { background: linear-gradient(135deg, #28a745, #34ce57); box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2); }
        .analytic-card.info { background: linear-gradient(135deg, #17a2b8, #1fc8e3); box-shadow: 0 10px 20px rgba(23, 162, 184, 0.2); }
        .analytic-card.warning { background: linear-gradient(135deg, #fba331, #ffc107); box-shadow: 0 10px 20px rgba(251, 163, 49, 0.2); }
        
        .card { border-radius: 12px; }
        .card-header { font-weight: 700; }
        
        .geo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        .geo-item:last-child { border-bottom: none; }
        .progress { height: 6px; border-radius: 3px; }
    </style>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let revenueChart = null;
    let funnelChart = null;

    function initAnalytics() {
        const dateRange = $('#date-range').val().split(' to ');
        let from = dateRange[0] || '';
        let to = dateRange[1] || '';
        
        const params = new URLSearchParams();
        if(from) params.append('from', from);
        if(to) params.append('to', to);
        
        const qStr = '?' + params.toString();

        // 1. Fetch Financial Stats
        fetch(`{{ route('seller.analytics.financial') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                $('#fin-gross').text(AIZ.extra.formatMoney(data.gross_sales));
                $('#fin-net').text(AIZ.extra.formatMoney(data.net_earnings));
                $('#fin-orders').text(data.order_count);
                $('#fin-payout').text(AIZ.extra.formatMoney(data.payout_ready));
            });

        // 2. Fetch Projected Stats
        fetch(`{{ route('seller.analytics.projected') }}`)
            .then(res => res.json())
            .then(data => {
                $('#proj-amount').text(AIZ.extra.formatMoney(data.projected_net));
            });

        // 3. Fetch Funnel
        fetch(`{{ route('seller.analytics.funnel') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                if(funnelChart) funnelChart.destroy();
                const ctx = document.getElementById('funnel-chart').getContext('2d');
                funnelChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Visits', 'Views', 'Carts', 'Checkout', 'Sales'],
                        datasets: [{
                            label: 'Interactions',
                            data: [data.visits, data.product_views, data.add_to_cart, data.checkout, data.purchased],
                            backgroundColor: [
                                'rgba(226, 65, 62, 0.8)', 
                                'rgba(59, 134, 203, 0.8)', 
                                'rgba(242, 202, 55, 0.8)', 
                                'rgba(251, 163, 49, 0.8)', 
                                'rgba(114, 182, 77, 0.8)'
                            ],
                            borderRadius: 6
                        }]
                    },
                    options: { 
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: { x: { display: false }, y: { grid: { display: false } } }
                    }
                });
            });

        // 4. Fetch Geo Stats
        fetch(`{{ route('seller.analytics.geo') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                const container = $('#geo-stats-container');
                container.empty();
                if(data.length === 0) {
                    container.append('<div class="text-center py-4 text-muted">{{ translate('No regional data available.') }}</div>');
                    return;
                }
                const maxRevenue = data[0].revenue || 1;
                data.forEach(item => {
                    const percent = (item.revenue / maxRevenue) * 100;
                    container.append(`
                        <div class="geo-item">
                            <div class="flex-grow-1 pr-3">
                                <span class="d-block fw-600 fs-13 text-dark mb-1">${item.city}</span>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: ${percent}%"></div>
                                </div>
                            </div>
                            <div class="text-right min-w-100px">
                                <span class="d-block fw-700 fs-13">${AIZ.extra.formatMoney(item.revenue)}</span>
                                <small class="text-muted d-block">${item.order_count} orders</small>
                            </div>
                        </div>
                    `);
                });
            });

        // 5. Fetch Top Products
        fetch(`{{ route('seller.analytics.top_products') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                const tbody = $('#top-products-tbody');
                tbody.empty();
                if(data.length === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center py-4 text-muted">{{ translate('No product performance data found.') }}</td></tr>');
                    return;
                }
                data.forEach(p => {
                    tbody.append(`
                        <tr class="has-transition">
                            <td class="pl-0 border-top-0">
                                <div class="d-flex align-items-center">
                                    <span class="text-dark fw-600 text-truncate-2" style="max-width: 250px;">${p.name}</span>
                                </div>
                            </td>
                            <td class="text-right fw-600 border-top-0">${p.views}</td>
                            <td class="text-right text-muted border-top-0">${p.cart_adds}</td>
                            <td class="text-right fw-700 text-success border-top-0">${p.sold}</td>
                            <td class="text-right border-top-0">
                                <span class="badge badge-inline badge-soft-primary">${p.conversion_percent}%</span>
                            </td>
                        </tr>
                    `);
                });
            });

        // 6. Fetch Revenue Trend
        fetch(`{{ route('seller.analytics.revenue_trend') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                if(revenueChart) revenueChart.destroy();
                const ctx = document.getElementById('revenue-chart').getContext('2d');
                
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 134, 203, 0.4)');
                gradient.addColorStop(1, 'rgba(59, 134, 203, 0)');

                revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [{
                            label: 'Revenue',
                            data: data.map(d => d.value),
                            borderColor: '#3b86cb',
                            borderWidth: 3,
                            fill: true,
                            backgroundColor: gradient,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#3b86cb',
                            pointHoverRadius: 6,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f5f5f5' }, beginAtZero: true },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
    }

    $(document).ready(function() {
        // AIZ datepicker
        $('.aiz-date-range').on('change', function() {
            initAnalytics();
        });
        
        initAnalytics();
    });
</script>
@endsection

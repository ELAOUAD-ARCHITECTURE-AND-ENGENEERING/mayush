@extends('seller.layouts.app')

@section('panel_content')
    <div class="row">
        <!-- KPI Cards -->
        <div class="col-md-3">
            <div class="card mb-4 bg-primary text-white">
                <div class="card-body">
                    <h6 class="mb-1">{{ translate('Total Visits') }}</h6>
                    <h2 class="mb-0 fw-600" id="kpi-total-visits">--</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 bg-success text-white">
                <div class="card-body">
                    <h6 class="mb-1">{{ translate('Unique Users') }}</h6>
                    <h2 class="mb-0 fw-600" id="kpi-unique-users">--</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 bg-warning text-white">
                <div class="card-body">
                    <h6 class="mb-1">{{ translate('Bounce Rate') }}</h6>
                    <h2 class="mb-0 fw-600" id="kpi-bounce-rate">--%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 bg-info text-white">
                <div class="card-body">
                    <h6 class="mb-1">{{ translate('Avg Duration') }}</h6>
                    <h2 class="mb-0 fw-600" id="kpi-avg-duration">--s</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Picker -->
    <div class="row align-items-center mb-4">
        <div class="col-md-4">
            <input type="text" class="form-control aiz-date-range" id="date-range" name="date" placeholder="Filter by date range">
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
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">{{ translate('Top Products') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table aiz-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ translate('Product Name') }}</th>
                                    <th class="text-right">{{ translate('Views') }}</th>
                                    <th class="text-right">{{ translate('Cart Adds') }}</th>
                                    <th class="text-right">{{ translate('Sold') }}</th>
                                    <th class="text-right">{{ translate('Conversion %') }}</th>
                                </tr>
                            </thead>
                            <tbody id="top-products-tbody">
                                <!-- Populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        // Fetch KPI stats
        fetch(`{{ route('seller.analytics.stats') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                $('#kpi-total-visits').text(data.total_visits || 0);
                $('#kpi-unique-users').text(data.unique_visitors || 0);
                $('#kpi-bounce-rate').text((data.bounce_rate || 0) + '%');
                $('#kpi-avg-duration').text((data.avg_duration_sec || 0) + 's');
            });

        // Fetch Funnel
        fetch(`{{ route('seller.analytics.funnel') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                if(funnelChart) funnelChart.destroy();
                const ctx = document.getElementById('funnel-chart').getContext('2d');
                funnelChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Visits', 'Product Views', 'Add to Cart', 'Checkout', 'Purchased'],
                        datasets: [{
                            label: 'Count',
                            data: [data.visits, data.product_views, data.add_to_cart, data.checkout, data.purchased],
                            backgroundColor: ['#e2413e', '#3b86cb', '#f2ca37', '#fba331', '#72b64d']
                        }]
                    },
                    options: { indexAxis: 'y' }
                });
            });

        // Fetch Top Products
        fetch(`{{ route('seller.analytics.top_products') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                const tbody = $('#top-products-tbody');
                tbody.empty();
                if(data.length === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center">No active products found to display performance.</td></tr>');
                    return;
                }
                data.forEach(p => {
                    tbody.append(`
                        <tr>
                            <td>${p.name}</td>
                            <td class="text-right">${p.views}</td>
                            <td class="text-right">${p.cart_adds}</td>
                            <td class="text-right">${p.sold}</td>
                            <td class="text-right">${p.conversion_percent}%</td>
                        </tr>
                    `);
                });
            });

        // Fetch Revenue Trend
        fetch(`{{ route('seller.analytics.revenue_trend') }}${qStr}`)
            .then(res => res.json())
            .then(data => {
                if(revenueChart) revenueChart.destroy();
                const ctx = document.getElementById('revenue-chart').getContext('2d');
                revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [{
                            label: 'Revenue',
                            data: data.map(d => d.value),
                            borderColor: '#3b86cb',
                            fill: true,
                            backgroundColor: 'rgba(59, 134, 203, 0.1)',
                            tension: 0.3
                        }]
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

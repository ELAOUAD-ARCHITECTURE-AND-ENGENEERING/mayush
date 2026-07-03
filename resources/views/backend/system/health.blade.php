@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-700 mb-1 text-dark">{{ translate('Operations & System Health') }}</h1>
                <p class="text-muted mb-0 fs-13">Real-time observability and anomaly detection.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url(config('pulse.path')) }}" target="_blank" class="btn btn-outline-primary btn-sm fw-600 rounded-pill px-3 shadow-sm d-flex align-items-center">
                    <i class="las la-chart-pie fs-18 mr-2"></i> Pulse Dashboard
                </a>
                <a href="{{ url(config('horizon.path')) }}" target="_blank" class="btn btn-dark btn-sm fw-600 rounded-pill px-3 shadow-sm d-flex align-items-center">
                    <i class="las la-server fs-18 mr-2"></i> Horizon Console
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row gutters-16 mb-4">
    <!-- Main Health Score -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 rounded-xl h-100 overflow-hidden" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-white-50 fs-13 fw-600 mb-0 text-uppercase tracking-wider">Payment Success (24h)</h6>
                    <div class="icon-shape bg-white-10 text-white rounded-circle">
                        <i class="las la-shield-alt fs-22"></i>
                    </div>
                </div>
                <div class="d-flex align-items-end mb-2">
                    <h2 class="text-white fs-36 fw-700 mb-0 lh-1">{{ $successRate }}<span class="fs-20">%</span></h2>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px; background-color: rgba(255,255,255,0.1);">
                        <div class="progress-bar {{ $successRate < 90 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $successRate }}%" aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mt-3 text-white-50 fs-12">
                    <i class="las {{ $successRate >= 95 ? 'la-arrow-up text-success' : 'la-arrow-down text-danger' }} mr-1"></i> Based on total daily attempts
                </div>
                <!-- Decorative background ring -->
                <div class="position-absolute" style="right: -20px; bottom: -20px; opacity: 0.05; z-index: -1;">
                    <i class="las la-shield-alt" style="font-size: 150px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Horizon Status -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 rounded-xl h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted fs-13 fw-600 mb-0 text-uppercase tracking-wider">Horizon Status</h6>
                    <span class="badge {{ $horizonStatus === 'Active' ? 'badge-soft-success' : 'badge-soft-danger' }} badge-inline rounded-pill px-3 py-1 fw-600">
                        {{ $horizonStatus }}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-shape {{ $horizonStatus === 'Active' ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="las la-server fs-24"></i>
                    </div>
                    <div>
                        <h3 class="fs-24 fw-700 mb-0 text-dark">{{ $horizonStatus === 'Active' ? 'Online' : 'Offline' }}</h3>
                        <p class="text-muted fs-12 mb-0 mt-1">Background Queue Workers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Failures -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 rounded-xl h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted fs-13 fw-600 mb-0 text-uppercase tracking-wider">Failed Jobs</h6>
                    <div class="icon-shape bg-soft-warning text-warning rounded-circle">
                        <i class="las la-exclamation-triangle fs-18"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h3 class="fs-28 fw-700 mb-0 {{ $failedJobs > 0 ? 'text-danger' : 'text-dark' }}">{{ $failedJobs }}</h3>
                        <p class="text-muted fs-12 mb-0 mt-1">Tasks requiring intervention</p>
                    </div>
                </div>
                @if($failedJobs > 0)
                <div class="mt-3">
                    <a href="{{ url(config('horizon.path') . '/failed') }}" target="_blank" class="text-danger fs-12 fw-600 hover-underline">Review Failed Jobs <i class="las la-angle-right"></i></a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Anomalies -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 rounded-xl h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-muted fs-13 fw-600 mb-0 text-uppercase tracking-wider">Critical Anomalies</h6>
                    <div class="icon-shape bg-soft-danger text-danger rounded-circle">
                        <i class="las la-radiation fs-18"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h3 class="fs-28 fw-700 mb-0 {{ $shippedUnpaid > 0 ? 'text-danger' : 'text-success' }}">{{ $shippedUnpaid }}</h3>
                        <p class="text-muted fs-12 mb-0 mt-1">Orders Shipped & Unpaid</p>
                    </div>
                </div>
                @if($shippedUnpaid > 0)
                <div class="mt-3">
                    <a href="{{ route('all_orders.index') }}" class="text-danger fs-12 fw-600 hover-underline">View Orders <i class="las la-angle-right"></i></a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row gutters-16">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-xl mb-4">
            <div class="card-header border-bottom-0 pt-4 pb-0 bg-transparent">
                <h6 class="mb-0 fs-16 fw-700 text-dark">Payment Reliability (7 Days)</h6>
                <p class="text-muted fs-13">Daily CMI attempts vs payment failures</p>
            </div>
            <div class="card-body p-4">
                <canvas id="paymentReliabilityChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-xl mb-4 h-100">
            <div class="card-header border-bottom-0 pt-4 pb-0 bg-transparent">
                <h6 class="mb-0 fs-16 fw-700 text-dark">System Queue Backlog</h6>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <div>
                            <span class="fs-12 text-muted text-uppercase fw-600 tracking-wider d-block">Pending Images</span>
                            <h4 class="fs-20 fw-700 text-dark mb-0">{{ $pendingImages }}</h4>
                        </div>
                        <i class="las la-images fs-24 text-info"></i>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, $pendingImages) }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <div>
                            <span class="fs-12 text-muted text-uppercase fw-600 tracking-wider d-block">Stuck Payments (24h)</span>
                            <h4 class="fs-20 fw-700 {{ $stuckPayments > 0 ? 'text-warning' : 'text-dark' }} mb-0">{{ $stuckPayments }}</h4>
                        </div>
                        <i class="las la-money-check-alt fs-24 text-warning"></i>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, $stuckPayments * 5) }}%"></div>
                    </div>
                    @if($stuckPayments > 0)
                        <small class="text-muted fs-11 mt-1 d-block"><i class="las la-info-circle"></i> Will be auto-expired by cron job.</small>
                    @endif
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <div>
                            <span class="fs-12 text-muted text-uppercase fw-600 tracking-wider d-block">Duplicate CMI Callbacks</span>
                            <h4 class="fs-20 fw-700 {{ $duplicateCmi > 0 ? 'text-primary' : 'text-dark' }} mb-0">{{ $duplicateCmi }}</h4>
                        </div>
                        <i class="las la-copy fs-24 text-primary"></i>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $duplicateCmi * 2) }}%"></div>
                    </div>
                    <small class="text-muted fs-11 mt-1 d-block"><i class="las la-shield-alt"></i> Successfully blocked by idempotency locks.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-xl { border-radius: 1rem !important; }
    .tracking-wider { letter-spacing: 0.05em; }
    .bg-white-10 { background-color: rgba(255, 255, 255, 0.1); }
    .hover-underline:hover { text-decoration: underline !important; }
</style>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('paymentReliabilityChart').getContext('2d');
        
        // Setup gradients
        let gradientPrimary = ctx.createLinearGradient(0, 0, 0, 400);
        gradientPrimary.addColorStop(0, 'rgba(15, 23, 42, 0.5)'); // slate-900
        gradientPrimary.addColorStop(1, 'rgba(15, 23, 42, 0.0)');
        
        let gradientDanger = ctx.createLinearGradient(0, 0, 0, 400);
        gradientDanger.addColorStop(0, 'rgba(239, 68, 68, 0.5)'); // red-500
        gradientDanger.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartDates) !!},
                datasets: [
                    {
                        label: 'Total Payment Attempts',
                        data: {!! json_encode($paymentChartData) !!},
                        borderColor: '#0F172A', // slate-900
                        backgroundColor: gradientPrimary,
                        borderWidth: 2,
                        pointBackgroundColor: '#0F172A',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'CMI Failures',
                        data: {!! json_encode($cmiChartData) !!},
                        borderColor: '#EF4444', // red-500
                        backgroundColor: gradientDanger,
                        borderWidth: 2,
                        pointBackgroundColor: '#EF4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            font: {
                                family: "'Inter', sans-serif",
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { family: "'Inter', sans-serif", size: 13 },
                        bodyFont: { family: "'Inter', sans-serif", size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        usePointStyle: true
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 11 },
                            color: '#64748B'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(241, 245, 249, 1)',
                            drawBorder: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 11 },
                            color: '#64748B',
                            stepSize: 1
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
            }
        });
    });
</script>
@endsection

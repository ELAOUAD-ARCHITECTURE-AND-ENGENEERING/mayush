@extends('backend.layouts.app')

@section('content')

<div class="card shadow-sm border-0">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('ONESSTA 3PL Shipping Dashboard') }}</h5>
    </div>
    <div class="card-body">
        <!-- KPI Metrics -->
        <div class="row gutters-10 mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-none border rounded-1 p-3 h-100">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="las la-box fs-36 text-primary"></i>
                        </div>
                        <div>
                            <div class="fs-24 fw-700 text-dark">{{ $stats['total_shipments'] ?? 0 }}</div>
                            <div class="text-muted fs-14">{{ translate('Total Shipments') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-none border rounded-1 p-3 h-100">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="las la-truck-moving fs-36 text-info"></i>
                        </div>
                        <div>
                            <div class="fs-24 fw-700 text-dark">{{ $stats['active_shipments'] ?? 0 }}</div>
                            <div class="text-muted fs-14">{{ translate('Active Shipments') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-none border rounded-1 p-3 h-100">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="las la-check-circle fs-36 text-success"></i>
                        </div>
                        <div>
                            <div class="fs-24 fw-700 text-dark">{{ $stats['delivered_shipments'] ?? 0 }}</div>
                            <div class="text-muted fs-14">{{ translate('Delivered Shipments') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('onessta.webhook-logs', ['status' => 'failed', 'range' => '24h', 'back' => url()->full()]) }}" class="text-reset text-decoration-none d-block h-100">
                    <div class="card shadow-none border rounded-1 p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="las la-exclamation-triangle fs-36 text-danger"></i>
                            </div>
                            <div>
                                <div class="fs-24 fw-700 text-danger">{{ $stats['failed_webhooks_24h'] ?? 0 }}</div>
                                <div class="text-muted fs-14">{{ translate('Webhook Errors (24h)') }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-5">
            <h6 class="mb-3">{{ translate('Quick Actions') }}</h6>
            <div class="d-flex flex-wrap">
                <a href="{{ route('onessta.shipments', ['back' => url()->full()]) }}" class="btn btn-primary mr-2 mb-2">
                    <i class="las la-list"></i> {{ translate('View All Shipments') }}
                </a>
                <button onclick="syncCities()" class="btn btn-outline-secondary mr-2 mb-2">
                    <i class="las la-sync"></i> {{ translate('Sync Cities') }}
                </button>
                <button onclick="pollTracking()" class="btn btn-outline-secondary mr-2 mb-2">
                    <i class="las la-satellite-dish"></i> {{ translate('Poll Tracking') }}
                </button>
                <button onclick="validateCredentials()" class="btn btn-outline-info mb-2">
                    <i class="las la-key"></i> {{ translate('Validate Credentials') }}
                </button>
            </div>
        </div>

        <!-- Recent Shipments Table -->
        <div>
            <h6 class="mb-3">{{ translate('Recent Shipments') }}</h6>
            <div class="table-responsive">
                <table class="table table-sm aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Code') }}</th>
                            <th>{{ translate('Receiver') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentShipments as $shipment)
                            @php
                                $badgeClass = 'badge-soft-secondary';
                                $status = strtoupper($shipment->status ?? '');
                                if (in_array($status, ['DELIVERED', 'SENT', 'PAID'])) {
                                    $badgeClass = 'badge-soft-success';
                                } elseif (in_array($status, ['NEW_PARCEL', 'CREATED'])) {
                                    $badgeClass = 'badge-soft-info';
                                } elseif (in_array($status, ['WAITING_PICKUP', 'IN_TRANSIT', 'PICKED_UP'])) {
                                    $badgeClass = 'badge-soft-warning';
                                } elseif (in_array($status, ['ERROR', 'FAILED', 'CANCELLED', 'REJECTED'])) {
                                    $badgeClass = 'badge-soft-danger';
                                }
                            @endphp
                            <tr>
                                <td>{{ $shipment->code ?: '-' }}</td>
                                <td>{{ $shipment->receiver ?: '-' }}</td>
                                <td>
                                    <span class="badge badge-inline {{ $badgeClass }}">
                                        {{ $shipment->status ?? translate('Unknown') }}
                                    </span>
                                </td>
                                <td>{{ $shipment->created_at ? $shipment->created_at->diffForHumans() : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted p-4">
                                    <i class="las la-inbox fs-40 opacity-40 mb-2 d-block"></i>
                                    {{ translate('No shipments found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    function syncCities() {
        fetch('{{ route('onessta.sync-cities') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(d => showAlert(d.status, d.message))
        .catch(e => showAlert('error', e.message));
    }

    function pollTracking() {
        fetch('{{ route('onessta.poll-tracking') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(d => showAlert(d.status, d.message))
        .catch(e => showAlert('error', e.message));
    }

    function validateCredentials() {
        fetch('{{ route('onessta.validate-credentials') }}')
        .then(r => r.json())
        .then(d => {
            if (d.valid) {
                showAlert('success', 'Credentials are valid!');
            } else if (d.configured) {
                showAlert('warning', 'Credentials are configured but invalid. Check your API keys.');
            } else {
                showAlert('error', 'Credentials are not configured. Set ONESSTA_TOKEN, ONESSTA_API_KEY, and ONESSTA_CLIENT_ID in .env');
            }
        })
        .catch(e => showAlert('error', e.message));
    }

    function showAlert(type, message) {
        if (type === 'success' || type === 'queued') {
            AIZ.plugins.notify('success', message);
        } else if (type === 'warning') {
            AIZ.plugins.notify('warning', message);
        } else {
            AIZ.plugins.notify('danger', message);
        }
    }
</script>
@endsection

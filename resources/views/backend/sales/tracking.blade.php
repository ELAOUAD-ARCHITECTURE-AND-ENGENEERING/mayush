@extends('backend.layouts.app')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* Dashboard Tracking Styling */
.tracking-card {
    border-radius: 15px;
    overflow: hidden;
}
.tracking-header {
    background: linear-gradient(135deg, #1d2b36 0%, #34495e 100%);
    color: white;
    padding: 2.5rem;
}

/* Progress Bar */
.track-progress {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 2.5rem 0;
}
.track-progress::before {
    content: '';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 0;
    width: 100%;
    height: 4px;
    background: #ebebeb;
    z-index: 1;
}
.track-progress .progress-fill {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 0;
    height: 4px;
    background: #0abb75;
    z-index: 2;
    transition: width 0.8s ease;
}
.track-step {
    position: relative;
    z-index: 3;
    text-align: center;
    flex-basis: 16%;
}
.track-step .icon {
    width: 38px;
    height: 38px;
    background: #fff;
    border: 3px solid #ebebeb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    color: #b0b0b0;
    transition: all 0.3s;
}
.track-step.completed .icon {
    border-color: #0abb75;
    background: #0abb75;
    color: white;
}
.track-step.active .icon {
    border-color: #2979ff;
    color: #2979ff;
    box-shadow: 0 0 10px rgba(41, 121, 255, 0.4);
}

/* Timeline */
.timeline-container {
    border-left: 2px solid #ebebeb;
    margin-left: 15px;
    padding-left: 25px;
}
.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -34px;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: white;
    border: 3px solid #2979ff;
}

#trackingMap {
    height: 550px;
    border-radius: 8px;
}
</style>

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Live Logistics Monitoring') }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('orders.show', encrypt($order->id)) }}" class="btn btn-secondary">
                <i class="las la-arrow-left"></i> {{ translate('Back to Order Details') }}
            </a>
            <a href="{{ route('orders.tracking.sync', encrypt($order->id)) }}" class="btn btn-primary">
                <i class="las la-sync"></i> {{ translate('Force Carrier Sync') }}
            </a>
        </div>
    </div>
</div>

<div class="card tracking-card shadow-sm mb-4">
    <div class="tracking-header text-center">
        <h4 class="mb-2">{{ translate('Tracking Order') }}: <strong>{{ $order->code }}</strong></h4>
        @if($order->tracking_code)
            <span class="badge badge-inline badge-light px-3 py-2 fs-14">
                {{ translate('Carrier Identifier') }}: {{ $order->tracking_code }}
            </span>
        @endif
    </div>
    
    <div class="card-body p-4">
        @php
            $statuses = ['pending', 'processing', 'shipped', 'in_transit', 'out_for_delivery', 'delivered'];
            $currentIdx = array_search($order->delivery_status, $statuses);
            if ($currentIdx === false) $currentIdx = 0;
            $progressPct = ($currentIdx / (count($statuses) - 1)) * 100;
        @endphp

        <div class="track-progress mx-xl-5">
            <div class="progress-fill" style="width: {{ $progressPct }}%;"></div>
            @foreach($statuses as $index => $status)
                @php
                    $isCompleted = $index <= $currentIdx;
                    $isActive = $index === $currentIdx && $status != 'delivered';
                @endphp
                <div class="track-step {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}">
                    <div class="icon">
                        @if($status == 'pending') <i class="las la-clock"></i>
                        @elseif($status == 'processing') <i class="las la-box"></i>
                        @elseif($status == 'shipped') <i class="las la-truck-loading"></i>
                        @elseif($status == 'in_transit') <i class="las la-route"></i>
                        @elseif($status == 'out_for_delivery') <i class="las la-shipping-fast"></i>
                        @elseif($status == 'delivered') <i class="las la-check-circle"></i>
                        @endif
                    </div>
                    <span class="d-none d-md-block fs-12 fw-600 text-capitalize">{{ str_replace('_', ' ', $status) }}</span>
                </div>
            @endforeach
        </div>

        <div class="row mt-5">
            <!-- Location Map -->
            <div class="col-lg-8">
                <div class="card shadow-none border h-100">
                    <div class="card-header py-3 bg-light">
                        <h5 class="mb-0 h6">{{ translate('Route Visualization') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="trackingMap"></div>
                    </div>
                </div>
            </div>

            <!-- Detailed Log -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card shadow-none border h-100">
                    <div class="card-header py-3 bg-light">
                        <h5 class="mb-0 h6">{{ translate('Logistics History') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($tracking_histories->isEmpty())
                            <div class="text-center py-5">
                                <i class="las la-search-location fs-40 text-muted opacity-50"></i>
                                <p class="mt-2 text-muted">{{ translate('No history recorded yet.') }}</p>
                            </div>
                        @else
                            <div class="timeline-container">
                                @foreach($tracking_histories->reverse() as $history)
                                <div class="timeline-item">
                                    <div class="pb-2 border-bottom mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="badge badge-inline badge-outline-primary text-capitalize fw-600 mb-2">
                                                {{ str_replace('_', ' ', $history->status) }}
                                            </span>
                                            <small class="text-muted">{{ $history->created_at->format('M d, H:i') }}</small>
                                        </div>
                                        <h6 class="fs-13 fw-700 text-dark mb-1">{{ $history->location_name ?: translate('Updating...') }}</h6>
                                        @if($history->notes)
                                            <p class="text-secondary fs-12 mb-0">{{ $history->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.trackingData = @json($tracking_histories->filter(fn($h) => $h->latitude && $h->longitude)->values());
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapBase = document.getElementById('trackingMap');
    if (!mapBase) return;

    var map = L.map('trackingMap').setView([20, 0], 2);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CartoDB',
        maxZoom: 19
    }).addTo(map);

    var points = window.trackingData;
    var latlngs = [];

    if (points.length > 0) {
        points.forEach(function(point, index) {
            var coords = [parseFloat(point.latitude), parseFloat(point.longitude)];
            latlngs.push(coords);
            var isLast = index === points.length - 1;
            
            var customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${isLast ? '#ff3b30' : '#007aff'}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.2);"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });

            var marker = L.marker(coords, {icon: customIcon}).addTo(map);
            marker.bindPopup(`<b>${point.location_name || point.status}</b><br>${point.notes || ''}`);
            if (isLast) marker.openPopup();
        });

        if (latlngs.length > 1) {
            L.polyline(latlngs, {color: '#007aff', weight: 3, dashArray: '5, 5'}).addTo(map);
        }
        map.fitBounds(L.latLngBounds(latlngs), {padding: [40, 40]});
    }
});
</script>
@endsection

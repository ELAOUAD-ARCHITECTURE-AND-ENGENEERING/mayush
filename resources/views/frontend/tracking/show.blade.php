@extends('frontend.layouts.app')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* Premium Tracking Aesthetics */
.tracking-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 3rem 0;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* Progress Bar */
.track-progress {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 3rem 0;
}
.track-progress::before {
    content: '';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 0;
    width: 100%;
    height: 6px;
    background: #e0e0e0;
    z-index: 1;
    border-radius: 10px;
}
.track-progress .progress-fill {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 0;
    height: 6px;
    background: #00e676;
    z-index: 2;
    border-radius: 10px;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
}
.track-step {
    position: relative;
    z-index: 3;
    text-align: center;
    flex-basis: 20%;
}
.track-step .icon {
    width: 40px;
    height: 40px;
    background: #fff;
    border: 3px solid #e0e0e0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 1.2rem;
    color: #9e9e9e;
    transition: all 0.3s;
}
.track-step.completed .icon {
    border-color: #00e676;
    background: #00e676;
    color: white;
    box-shadow: 0 0 15px rgba(0, 230, 118, 0.5);
}
.track-step.active .icon {
    border-color: #2979ff;
    color: #2979ff;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(41, 121, 255, 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(41, 121, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(41, 121, 255, 0); }
}

/* Timeline */
.timeline-container {
    border-left: 3px solid #e0e0e0;
    margin-left: 20px;
    padding-left: 30px;
    position: relative;
}
.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -40px;
    top: 0;
    width: 17px;
    height: 17px;
    border-radius: 50%;
    background: white;
    border: 4px solid #2979ff;
}
.timeline-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}
.timeline-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

#trackingMap {
    height: 500px;
    border-radius: 12px;
    z-index: 10;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
</style>

<div class="container py-5">
    <!-- Header -->
    <div class="tracking-header text-center px-4">
        <h2 class="fw-bold mb-3">{{ translate('Live Order Tracking') }}</h2>
        <p class="fs-18 opacity-80 mb-0">{{ translate('Order Code') }}: <strong>{{ $order->code }}</strong></p>
        @if($order->tracking_code)
            <p class="fs-15 opacity-80 mt-2">{{ translate('Carrier Code') }}: <strong>{{ $order->tracking_code }}</strong></p>
        @endif
        
        <div class="mt-4">
            <a href="{{ route('orders.tracking.sync', encrypt($order->id)) }}" class="btn btn-light rounded-pill px-4 fw-600">
                <i class="las la-sync"></i> {{ translate('Sync Carrier Updates') }}
            </a>
        </div>
    </div>

    <!-- Progress Milestones -->
    @php
        $statuses = ['pending', 'processing', 'shipped', 'in_transit', 'out_for_delivery', 'delivered'];
        $currentIdx = array_search($order->delivery_status, $statuses);
        if ($currentIdx === false) $currentIdx = 0;
        $progressPct = ($currentIdx / (count($statuses) - 1)) * 100;
    @endphp

    <div class="bg-white p-5 rounded-box shadow-sm mb-5">
        <h4 class="fw-600 mb-4">{{ translate('Delivery Progress') }}</h4>
        <div class="track-progress">
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
                    <span class="d-block fw-600 text-capitalize">{{ str_replace('_', ' ', $status) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="row">
        <!-- Interactive Map -->
        <div class="col-lg-7 mb-4">
            <h4 class="fw-600 mb-4">{{ translate('Live Location Map') }}</h4>
            <div id="trackingMap"></div>
        </div>
        
        <!-- Timeline -->
        <div class="col-lg-5">
            <h4 class="fw-600 mb-4">{{ translate('Tracking History') }}</h4>
            
            <div class="bg-white p-4 rounded-box shadow-sm h-100 placeholder-container overflow-hidden">
                @if($tracking_histories->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="las la-history fs-40 mb-3 opacity-50"></i>
                        <p>{{ translate('No advanced tracking history available yet.') }}</p>
                    </div>
                @else
                    <div class="timeline-container">
                        @foreach($tracking_histories->reverse() as $history)
                        <div class="timeline-item">
                            <div class="timeline-card border border-soft-secondary">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-inline badge-primary fw-600 text-capitalize">{{ str_replace('_', ' ', $history->status) }}</span>
                                    <small class="text-muted"><i class="las la-clock"></i> {{ $history->created_at->format('d M, h:i A') }}</small>
                                </div>
                                @if($history->location_name)
                                    <h6 class="fw-600 text-dark mb-1"><i class="las la-map-marker text-danger"></i> {{ $history->location_name }}</h6>
                                @endif
                                @if($history->notes)
                                    <p class="text-muted fs-13 mb-0">{{ $history->notes }}</p>
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

<!-- Raw Data for JS Injection -->
<script>
    window.trackingData = @json($tracking_histories->filter(function($h) { return $h->latitude && $h->longitude; })->values());
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapBase = document.getElementById('trackingMap');
    if (!mapBase) return;

    // Default View (Global/Fallback)
    var map = L.map('trackingMap').setView([34.0522, -118.2437], 10);
    
    // Add Premium Dark/Light Map Tiles (CartoDB Positron for modern feel)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors & CartoDB',
        maxZoom: 19
    }).addTo(map);

    var points = window.trackingData;
    var latlngs = [];

    if (points.length > 0) {
        points.forEach(function(point, index) {
            var coords = [parseFloat(point.latitude), parseFloat(point.longitude)];
            latlngs.push(coords);

            // Create marker
            var isLast = index === points.length - 1;
            
            // Custom Pulse Icon for the current active location
            var customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color: ${isLast ? '#ff3b30' : '#007aff'}; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.3); ${isLast ? 'animation: pulse 2s infinite;' : ''}"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            var marker = L.marker(coords, {icon: customIcon}).addTo(map);
            marker.bindPopup(`<b>${point.location_name || point.status}</b><br>${point.notes || ''}`);
            
            if (isLast) marker.openPopup();
        });

        // Draw Polyline connecting the stops
        if (latlngs.length > 1) {
            var polyline = L.polyline(latlngs, {
                color: '#007aff', 
                weight: 4, 
                opacity: 0.7, 
                dashArray: '10, 10', 
                lineCap: 'round'
            }).addTo(map);
        }

        // Auto-fit map to bounds of all points
        map.fitBounds(L.latLngBounds(latlngs), {padding: [50, 50]});
    }
});
</script>
@endsection

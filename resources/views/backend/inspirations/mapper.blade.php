@extends('backend.layouts.app')

@push('css')
<style>
    .mapper-container { position: relative; display: inline-block; max-width: 100%; }
    .mapper-container img { max-width: 100%; height: auto; display: block; }

    .hotspot-marker {
        position: absolute;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #1F2A3A;
        color: #fff;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
        transform: translate(-50%, -50%);
        cursor: pointer;
        user-select: none;
        z-index: 10;
        transition: transform 0.15s ease-out;
        tabindex: 0;
    }
    .hotspot-marker:hover { transform: translate(-50%, -50%) scale(1.15); }
    .hotspot-marker.active { animation: marker-pulse 1s ease-in-out infinite; }
    .hotspot-marker.dragging { opacity: 0.6; cursor: grabbing; }
    .hotspot-marker.placing { animation: marker-appear 0.3s ease-out; }

    @keyframes marker-pulse {
        0%, 100% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.15); }
    }
    @keyframes marker-appear {
        from { transform: translate(-50%, -50%) scale(0); }
        to { transform: translate(-50%, -50%) scale(1); }
    }

    .mapper-container.mode-place { cursor: crosshair; }
    .mapper-container.mode-drag .hotspot-marker { cursor: grab; }

    .mode-btn.active { background: #1F2A3A; color: #fff; }

    .search-modal {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 1000;
        align-items: center; justify-content: center;
    }
    .search-modal.open { display: flex; }
    .search-panel {
        background: #fff; border-radius: 12px; width: 420px; max-height: 500px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2); overflow: hidden;
    }
    .search-input { width: 100%; padding: 12px 16px; border: none; border-bottom: 1px solid #eee; font-size: 15px; outline: none; }
    .search-results { max-height: 380px; overflow-y: auto; }
    .search-result-item {
        display: flex; align-items: center; gap: 12px; padding: 10px 16px; cursor: pointer; border-bottom: 1px solid #f5f5f5;
    }
    .search-result-item:hover, .search-result-item.selected { background: #f8f4ef; }
    .search-result-item img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; }
    .search-result-item .name { font-weight: 500; font-size: 14px; }
    .search-result-item .price { font-size: 13px; color: #666; }
    .search-result-item .stock-badge { font-size: 11px; padding: 2px 6px; border-radius: 4px; }
    .stock-badge.in-stock { background: #d4edda; color: #155724; }
    .stock-badge.out-of-stock { background: #f8d7da; color: #721c24; }

    .save-indicator { font-size: 13px; font-weight: 500; }
    .save-indicator.saved { color: #28a745; }
    .save-indicator.saving { color: #ffc107; }
    .save-indicator.error { color: #dc3545; }

    .item-list-row { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
    .item-list-row img { width: 36px; height: 36px; border-radius: 6px; object-fit: cover; }
    .item-list-row .number { width: 24px; height: 24px; border-radius: 50%; background: #1F2A3A; color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .item-list-row .name { font-weight: 500; font-size: 14px; flex: 1; }
    .item-list-row .unavailable { opacity: 0.5; }
    .item-warning { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px; }

    .toast-notification {
        position: fixed; bottom: 24px; right: 24px; background: #1F2A3A; color: #fff;
        padding: 10px 20px; border-radius: 8px; font-size: 14px; z-index: 2000;
        animation: toast-in 0.3s ease-out;
    }
    @keyframes toast-in { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    .preview-frame { margin: 0 auto; border: 2px solid #ddd; border-radius: 8px; overflow: hidden; }
</style>
@endpush

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <a href="{{ route('inspirations.edit', $inspiration) }}" class="text-muted mr-2"><i class="las la-arrow-left"></i></a>
            <span class="h3">{{ $inspiration->title_fr }} — {{ translate('Mapper') }}</span>
        </div>
        <div class="col-md-6 text-md-right">
            <button type="button" class="btn btn-sm mode-btn active" data-mode="place" onclick="mapper.switchMode('place')">+ {{ translate('Place') }}</button>
            <button type="button" class="btn btn-sm mode-btn" data-mode="drag" onclick="mapper.switchMode('drag')">↔ {{ translate('Move') }}</button>
            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="mapper.togglePreview()">{{ translate('Preview') }}</button>
            <span class="save-indicator saved ml-3" id="saveIndicator">{{ translate('Saved') }} ✓</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="mapper-container mode-place" id="mapperContainer">
            <img src="{{ asset('storage/' . $inspiration->hero_image) }}" alt="{{ $inspiration->title_fr }}" id="mapperImage" draggable="false">
            {{-- Markers rendered by JS --}}
        </div>
    </div>
</div>

<div class="card" id="itemsCard">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Associated Products') }} (<span id="itemCount">{{ $inspiration->items->count() }}</span>)</h5>
    </div>
    <div class="card-body p-0">
        @php
            $unavailableCount = $inspiration->items->filter(fn($item) => $item->product && !($item->product->published && $item->product->approved))->count();
        @endphp
        @if($unavailableCount > 0)
            <div class="item-warning m-3">
                ⚠ {{ $unavailableCount }} {{ translate('produit(s) indisponible(s)') }}
            </div>
        @endif
        <div id="itemsList">
            {{-- Rendered by JS --}}
        </div>
    </div>
</div>

{{-- Product Search Modal --}}
<div class="search-modal" id="searchModal">
    <div class="search-panel">
        <input type="text" class="search-input" id="searchInput" placeholder="{{ translate('Search for a product...') }}" autocomplete="off">
        <div class="search-results" id="searchResults"></div>
    </div>
</div>

<script>
    window.MAPPER_CONFIG = {
        containerId: 'mapperContainer',
        imageId: 'mapperImage',
        inspirationId: {{ $inspiration->id }},
        csrfToken: '{{ csrf_token() }}',
        searchUrl: '{{ route("products.search") }}',
        storeUrl: '{{ route("inspirations.hotspots.store", $inspiration) }}',
        updateUrlTemplate: '{{ route("inspirations.hotspots.update", [$inspiration, "__HOTSPOT_ID__"]) }}',
        destroyUrlTemplate: '{{ route("inspirations.hotspots.destroy", [$inspiration, "__HOTSPOT_ID__"]) }}',
        existingItems: @json($inspiration->items->map(fn($item) => [
            'id' => $item->id,
            'hotspot_id' => $item->hotspot?->id,
            'display_order' => $item->display_order,
            'x' => $item->hotspot ? (float) $item->hotspot->x : null,
            'y' => $item->hotspot ? (float) $item->hotspot->y : null,
            'product' => [
                'id' => $item->product?->id,
                'name' => $item->product?->getTranslation('name', 'fr') ?? 'Unknown',
                'price' => $item->product ? format_price(convert_price($item->product->unit_price)) : '—',
                'image' => $item->product ? uploaded_asset($item->product->thumbnail_img) : '',
                'available' => (bool) ($item->product?->published && $item->product?->approved),
            ],
        ])->values()),
        translations: {
            saved: '{{ translate("Saved") }} ✓',
            saving: '{{ translate("Saving...") }}',
            error: '{{ translate("Error") }} ✗',
            noResults: '{{ translate("No products found") }}',
            loading: '{{ translate("Searching...") }}',
            undone: '{{ translate("Undone") }}',
            deleteConfirm: '{{ translate("Delete this hotspot?") }}',
        },
    };
</script>
<script src="{{ asset('js/inspiration-mapper.js') }}"></script>
@endsection

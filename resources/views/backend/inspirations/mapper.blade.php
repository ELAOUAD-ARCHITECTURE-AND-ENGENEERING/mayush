@extends('backend.layouts.app')

@push('css')
<style>
    .mapper-stage { margin: 0 auto; transition: max-width .2s ease; }
    .mapper-stage.preview-frame { border: 2px solid #ddd; border-radius: 8px; overflow: hidden; }
    .mapper-container { position: relative; display: inline-block; max-width: 100%; }
    .mapper-container img { display: block; max-width: 100%; height: auto; }
    .mapper-container.mode-place { cursor: crosshair; }
    .mapper-container.mode-drag .hotspot-marker { cursor: grab; }
    .hotspot-marker { position: absolute; z-index: 10; display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border: 2px solid #fff; border-radius: 50%; background: #1f2a3a; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.25); transform: translate(-50%,-50%); font-size: 12px; font-weight: 700; cursor: pointer; user-select: none; transition: transform 150ms ease-out; }
    .hotspot-marker:hover { transform: translate(-50%,-50%) scale(1.15); }
    .hotspot-marker.active { animation: marker-pulse 1s ease-in-out infinite; }
    .hotspot-marker.placing { animation: marker-appear 300ms ease-out; }
    .hotspot-marker.dragging, .drag-ghost { opacity: .6; cursor: grabbing; }
    .hotspot-marker.snapping { transition: left 150ms ease-out, top 150ms ease-out, transform 150ms ease-out; }
    .drag-ghost { pointer-events: none; z-index: 20; }
    .marker-tooltip { display: none; position: absolute; bottom: 34px; left: 50%; min-width: 180px; padding: 7px; border-radius: 7px; background: #1f2a3a; color: #fff; box-shadow: 0 3px 12px rgba(0,0,0,.3); transform: translateX(-50%); align-items: center; gap: 7px; pointer-events: none; }
    .marker-tooltip img { width: 30px; height: 30px; border-radius: 4px; object-fit: cover; }
    .hotspot-marker:hover .marker-tooltip, .hotspot-marker:focus .marker-tooltip { display: flex; }
    .marker-context-menu { position: absolute; z-index: 50; min-width: 150px; padding: 6px; border-radius: 7px; background: #fff; box-shadow: 0 4px 18px rgba(0,0,0,.25); transform: translate(-50%,20px); }
    .marker-context-menu button { display: block; width: 100%; padding: 7px 9px; border: 0; background: transparent; text-align: left; }
    @keyframes marker-pulse { 0%,100% { transform: translate(-50%,-50%) scale(1); } 50% { transform: translate(-50%,-50%) scale(1.15); } }
    @keyframes marker-appear { from { transform: translate(-50%,-50%) scale(0); } to { transform: translate(-50%,-50%) scale(1); } }

    .mode-btn.active { background: #1f2a3a; color: #fff; }
    .search-modal { display: none; position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,.5); align-items: center; justify-content: center; }
    .search-modal.open { display: flex; }
    .search-panel { width: 420px; max-width: calc(100vw - 32px); max-height: 500px; overflow: hidden; border-radius: 12px; background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,.2); }
    .search-input { width: 100%; padding: 12px 16px; border: 0; border-bottom: 1px solid #eee; outline: 0; font-size: 15px; }
    .search-results { max-height: 380px; overflow-y: auto; }
    .search-section-title { padding: 8px 16px; background: #f6f7f9; color: #59606d; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .search-result-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-bottom: 1px solid #f5f5f5; cursor: pointer; }
    .search-result-item:hover, .search-result-item.selected { background: #f8f4ef; }
    .search-result-item img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; }
    .search-result-item .name { font-size: 14px; font-weight: 500; }
    .search-result-item .price { color: #666; font-size: 13px; }
    .stock-badge { padding: 2px 6px; border-radius: 4px; font-size: 11px; }
    .stock-badge.in-stock { background: #d4edda; color: #155724; }
    .stock-badge.out-of-stock { background: #f8d7da; color: #721c24; }

    .save-indicator { font-size: 13px; font-weight: 500; }
    .save-indicator.saved { color: #28a745; }
    .save-indicator.saving { color: #a66d00; }
    .save-indicator.error { color: #dc3545; }
    .save-retry { display: none; }
    .item-list-row { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
    .item-list-row img { width: 36px; height: 36px; border-radius: 6px; object-fit: cover; }
    .item-list-row .number { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; background: #1f2a3a; color: #fff; font-size: 11px; font-weight: 700; }
    .item-list-row .name { flex: 1; font-size: 14px; font-weight: 500; }
    .item-list-row.unavailable { opacity: .65; }
    .item-warning { margin: 12px; padding: 8px 12px; border: 1px solid #ffc107; border-radius: 6px; background: #fff3cd; font-size: 13px; }
    .toast-notification { position: fixed; right: 24px; bottom: 24px; z-index: 2000; padding: 10px 20px; border-radius: 8px; background: #1f2a3a; color: #fff; font-size: 14px; animation: toast-in 300ms ease-out; }
    @keyframes toast-in { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .preview-controls { display: none; }
    .preview-mode .preview-controls { display: inline-flex; }
    .preview-mode .edit-only { display: none !important; }
    .preview-mode #itemsList { display: flex; flex-wrap: wrap; padding: 4px; }
    .preview-mode .item-list-row { flex-wrap: wrap; width: calc(50% - 8px); margin: 4px; border: 1px solid #eee; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3" id="mapperToolbar">
    <div class="row align-items-center">
        <div class="col-md-6">
            <a href="{{ route('inspirations.edit', $inspiration) }}" class="text-muted mr-2"><i class="las la-arrow-left"></i></a>
            <span class="h3">{{ $inspiration->title_fr }} - {{ translate('Mapper') }}</span>
        </div>
        <div class="col-md-6 text-md-right">
            <button type="button" class="btn btn-sm mode-btn active edit-only" data-mode="place" onclick="mapper.switchMode('place')">+ {{ translate('Place') }}</button>
            <button type="button" class="btn btn-sm mode-btn edit-only" data-mode="drag" onclick="mapper.switchMode('drag')">{{ translate('Move') }}</button>
            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="mapper.togglePreview()">{{ translate('Preview') }}</button>
            <span class="preview-controls btn-group ml-2" id="previewControls" role="group" aria-label="{{ translate('Preview width') }}">
                <button type="button" class="btn btn-sm btn-soft-secondary" data-width="390">{{ translate('Mobile') }}</button>
                <button type="button" class="btn btn-sm btn-soft-secondary" data-width="768">{{ translate('Tablet') }}</button>
                <button type="button" class="btn btn-sm btn-soft-secondary" data-width="1440">{{ translate('Desktop') }}</button>
            </span>
            <span class="save-indicator saved ml-3 edit-only" id="saveIndicator">{{ translate('Saved') }}</span>
            <button type="button" class="btn btn-link btn-sm save-retry edit-only" id="saveRetry">{{ translate('Retry') }}</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="mapper-stage" id="mapperStage">
            <div class="mapper-container mode-place" id="mapperContainer">
                <img src="{{ asset('storage/'.$inspiration->hero_image) }}" alt="{{ $inspiration->title_fr }}" id="mapperImage" draggable="false">
            </div>
        </div>
    </div>
</div>

<div class="card" id="itemsCard">
    <div class="card-header"><h5 class="mb-0 h6">{{ translate('Associated Products') }} (<span id="itemCount">{{ $inspiration->items->count() }}</span>)</h5></div>
    <div class="card-body p-0">
        <div class="item-warning" id="unavailableWarning" hidden></div>
        <div id="itemsList"></div>
    </div>
</div>

<div class="search-modal" id="searchModal" role="dialog" aria-modal="true" aria-labelledby="searchDialogTitle">
    <div class="search-panel" tabindex="-1">
        <label class="sr-only" id="searchDialogTitle" for="searchInput">{{ translate('Search for a product') }}</label>
        <input type="text" class="search-input" id="searchInput" placeholder="{{ translate('Search for a product...') }}" autocomplete="off">
        <div class="search-results" id="searchResults"></div>
    </div>
</div>
<div class="sr-only" id="mapperLiveRegion" aria-live="polite" aria-atomic="true"></div>

<script>
window.MAPPER_CONFIG = {
    containerId: 'mapperContainer',
    imageId: 'mapperImage',
    inspirationId: {{ $inspiration->id }},
    csrfToken: '{{ csrf_token() }}',
    searchUrl: '{{ route('products.search') }}',
    storeUrl: '{{ route('inspirations.hotspots.store', $inspiration) }}',
    updateUrlTemplate: '{{ route('inspirations.hotspots.update', [$inspiration, '__HOTSPOT_ID__']) }}',
    destroyUrlTemplate: '{{ route('inspirations.hotspots.destroy', [$inspiration, '__HOTSPOT_ID__']) }}',
    existingItems: @json($mapperItems),
    translations: {
        saved: '{{ translate('Saved') }}',
        saving: '{{ translate('Saving...') }}',
        error: '{{ translate('Error') }}',
        noResults: '{{ translate('No products found') }}',
        loading: '{{ translate('Searching...') }}',
        undone: '{{ translate('Undone') }}',
        redone: '{{ translate('Redone') }}',
        deleteConfirm: '{{ translate('Delete this hotspot?') }}',
        unavailable: '{{ translate('product(s) unavailable') }}',
        recents: '{{ translate('Recent products') }}',
        replace: '{{ translate('Replace product') }}',
        delete: '{{ translate('Delete') }}',
        saveFailed: '{{ translate('Unable to save changes') }}',
    },
};
</script>
<script src="{{ asset('js/inspiration-mapper.js') }}"></script>
@endsection

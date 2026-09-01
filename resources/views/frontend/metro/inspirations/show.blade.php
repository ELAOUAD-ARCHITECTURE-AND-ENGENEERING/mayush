@extends('frontend.layouts.app')

@php
    $sysLang = get_system_language();
    $lang = $sysLang ? $sysLang->code : App::getLocale();
    $title = $inspiration->getTitle($lang);
    $subtitle = $inspiration->getSubtitle($lang);
    $desc = $inspiration->getDescription($lang);
    $heroImage = $inspiration->hero_image ? Storage::disk('public')->url($inspiration->hero_image) : static_asset('assets/img/placeholder.jpg');
@endphp

@section('meta_title'){{ $title }} | Mayush Inspirations @stop
@section('meta_description'){{ $subtitle ?: $desc }}@stop

@section('content')
<div class="py-4 bg-light-subtle">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-transparent p-0 mb-0 fs-13">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">{{ translate('Home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspirations.all') }}" class="text-secondary">{{ $lang == 'ar' ? 'إلهامات الديكور' : 'Inspirations' }}</a></li>
                <li class="breadcrumb-item active text-dark fw-600" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>

        <!-- Inspiration Header -->
        <div class="mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                <div>
                    <span class="badge badge-warning font-weight-bold px-2 py-1 mb-2 text-white" style="background: #D6A24E; font-size: 11px;">
                        {{ $lang == 'ar' ? 'تنسيق متناسق' : 'Shop The Look' }}
                    </span>
                    <h1 class="fs-24 fs-md-32 fw-800 text-dark mb-1">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="fs-14 fs-md-16 text-secondary mb-0">{{ $subtitle }}</p>
                    @endif
                </div>
                <a href="{{ route('inspirations.all') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="las {{ $lang == 'ar' ? 'la-arrow-right' : 'la-arrow-left' }} mr-1"></i>
                    {{ $lang == 'ar' ? 'الرجوع للإلهامات' : 'Toutes les inspirations' }}
                </a>
            </div>
            @if($desc)
                <p class="fs-14 text-secondary mt-2 max-w-800px">{{ $desc }}</p>
            @endif
        </div>

        <div class="row gutters-16">
            <!-- Main Scene Image with Interactive Hotspots -->
            <div class="col-lg-8 mb-4">
                <div class="bg-white border rounded-lg overflow-hidden shadow-sm position-relative">
                    <div class="position-relative inspiration-detail-scene-wrap">
                        <img
                            src="{{ $heroImage }}"
                            alt="{{ $title }}"
                            class="w-100 img-fluid d-block"
                            id="inspirationDetailImg"
                        >
                        <!-- Hotspots -->
                        @foreach($inspiration->items as $item)
                            @if($item->hotspot && $item->product)
                                @php
                                    $p = $item->product;
                                    $pName = ($lang == 'ar' && $item->custom_title_ar) ? $item->custom_title_ar : (($lang != 'ar' && $item->custom_title_fr) ? $item->custom_title_fr : $p->getTranslation('name', $lang));
                                    $posX = $item->hotspot->x * 100;
                                    $posY = $item->hotspot->y * 100;
                                @endphp
                                <div class="web-hotspot-pin" style="left: {{ $posX }}%; top: {{ $posY }}%;" data-target-product="product-card-{{ $p->id }}">
                                    <button class="web-hotspot-btn" type="button" aria-label="{{ $pName }}" onclick="highlightProductCard('product-card-{{ $p->id }}')">
                                        <span>{{ $loop->iteration }}</span>
                                        <span class="hotspot-pulse-ring"></span>
                                    </button>
                                    <div class="web-hotspot-tooltip">
                                        <a href="{{ route('product', $p->slug) }}" class="d-flex align-items-center text-dark text-decoration-none">
                                            <img src="{{ uploaded_asset($p->thumbnail_img) }}" alt="{{ $pName }}" class="size-48px rounded object-fit-cover mr-2 flex-shrink-0" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                            <div class="min-w-0">
                                                <div class="fs-12 fw-700 text-truncate-2 line-height-1-2 mb-1">{{ $pName }}</div>
                                                <div class="fs-12 fw-800 text-primary">{{ home_discounted_base_price($p) }}</div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Product Sidebar List -->
            <div class="col-lg-4">
                <div class="bg-white border rounded-lg p-3 p-md-4 shadow-sm mb-4">
                    <h3 class="fs-16 fw-700 text-dark mb-3 d-flex align-items-center justify-content-between">
                        <span>{{ $lang == 'ar' ? 'المنتجات في هذه الغرفة' : 'Articles de cette ambiance' }}</span>
                        <span class="badge badge-pill badge-light fs-12">{{ $inspiration->items->count() }}</span>
                    </h3>

                    <div class="inspiration-sidebar-products">
                        @foreach($inspiration->items as $item)
                            @if($item->product)
                                @php
                                    $prod = $item->product;
                                    $prodName = ($lang == 'ar' && $item->custom_title_ar) ? $item->custom_title_ar : (($lang != 'ar' && $item->custom_title_fr) ? $item->custom_title_fr : $prod->getTranslation('name', $lang));
                                    $isOutOfStock = ($prod->current_stock <= 0);
                                @endphp
                                <div class="p-2 mb-2 rounded border bg-light-subtle has-transition inspiration-product-item {{ $isOutOfStock ? 'opacity-60' : '' }}" id="product-card-{{ $prod->id }}">
                                    <div class="d-flex align-items-center">
                                        <!-- Number Badge + Thumbnail -->
                                        <div class="position-relative size-56px flex-shrink-0 mr-3 rounded overflow-hidden">
                                            <a href="{{ route('product', $prod->slug) }}">
                                                <img src="{{ uploaded_asset($prod->thumbnail_img) }}" alt="{{ $prodName }}" class="w-100 h-100 object-fit-cover" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                            </a>
                                            <span class="position-absolute bg-dark text-white rounded-circle font-weight-bold d-flex align-items-center justify-content-center" style="top: 2px; left: 2px; width: 18px; height: 18px; font-size: 10px;">
                                                {{ $loop->iteration }}
                                            </span>
                                        </div>

                                        <!-- Info -->
                                        <div class="min-w-0 flex-grow-1 pr-2">
                                            <h4 class="fs-13 fw-700 mb-1 text-truncate">
                                                <a href="{{ route('product', $prod->slug) }}" class="text-dark hov-text-primary">
                                                    {{ $prodName }}
                                                </a>
                                            </h4>
                                            <div class="fs-13 fw-800 text-primary mb-1">
                                                {{ home_discounted_base_price($prod) }}
                                            </div>
                                            @if($isOutOfStock)
                                                <span class="badge badge-danger" style="font-size: 10px;">{{ translate('Out of Stock') }}</span>
                                            @endif
                                        </div>

                                        <!-- Action -->
                                        <div class="flex-shrink-0">
                                            <a href="{{ route('product', $prod->slug) }}" class="btn btn-sm btn-primary rounded-circle size-32px p-0 d-flex align-items-center justify-content-center" title="{{ translate('View Details') }}">
                                                <i class="las la-eye fs-16"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.inspiration-detail-scene-wrap {
    background: #1f2a3a;
    position: relative;
    user-select: none;
}
.web-hotspot-pin {
    position: absolute;
    transform: translate(-50%, -50%);
    z-index: 15;
}
.web-hotspot-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #1f2a3a;
    color: #ffffff;
    border: 2px solid #ffffff;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    position: relative;
    transition: transform 0.2s ease, background 0.2s ease;
    padding: 0;
}
.web-hotspot-btn:hover {
    background: #D6A24E;
    transform: scale(1.15);
}
.hotspot-pulse-ring {
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 50%;
    border: 2px solid rgba(214, 162, 78, 0.7);
    animation: hotspotPulse 2s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
}
.web-hotspot-tooltip {
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    transform: translateX(-50%) translateY(6px);
    background: #ffffff;
    border: 1px solid rgba(226, 224, 214, 0.9);
    border-radius: 10px;
    padding: 8px 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    width: 200px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease, transform 0.25s ease;
    z-index: 25;
}
.web-hotspot-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-width: 6px;
    border-style: solid;
    border-color: #ffffff transparent transparent transparent;
}
.web-hotspot-pin:hover .web-hotspot-tooltip,
.web-hotspot-pin:focus-within .web-hotspot-tooltip {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(-50%) translateY(0);
}
.inspiration-product-item.active-highlight {
    border-color: #D6A24E !important;
    background-color: rgba(214, 162, 78, 0.1) !important;
    box-shadow: 0 0 12px rgba(214, 162, 78, 0.35);
}
</style>

<script>
function highlightProductCard(cardId) {
    var el = document.getElementById(cardId);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    el.classList.add('active-highlight');
    setTimeout(function() {
        el.classList.remove('active-highlight');
    }, 1500);
}
</script>
@endsection

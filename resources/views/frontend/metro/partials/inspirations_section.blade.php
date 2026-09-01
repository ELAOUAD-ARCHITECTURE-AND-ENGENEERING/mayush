@php
    $homeInspirations = \App\Models\Inspiration::query()
        ->published()
        ->where('show_on_home', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->take(6)
        ->with(['items' => function($q) {
            $q->where('is_visible', true)->orderBy('display_order')->with(['product.stocks', 'product.taxes', 'hotspot']);
        }, 'hotspots'])
        ->get();
    $sysLang = get_system_language();
    $lang = $sysLang ? $sysLang->code : App::getLocale();
@endphp

@if($homeInspirations->isNotEmpty())
<section id="home_inspirations_section" class="mb-4 mt-3 mt-md-4">
    <div class="container">
        <!-- Section Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h2 class="fs-22 fs-md-28 fw-800 mb-1 text-dark">
                    {{ $lang == 'ar' ? 'إلهامات الديكور وتنسيق الغرف' : 'Inspiration & Ambiances' }}
                </h2>
                <p class="fs-13 fs-md-14 text-secondary mb-0">
                    {{ $lang == 'ar' ? 'اكتشف غرفاً وأفكاراً ملهمة وتسوق كل قطعة بنقرة واحدة' : 'Explorez nos décors complets et shoppez chaque pièce en un clic' }}
                </p>
            </div>
            <a href="{{ route('inspirations.all') }}" class="fs-13 fs-md-14 fw-700 text-primary hov-text-primary d-inline-flex align-items-center gap-1">
                <span>{{ $lang == 'ar' ? 'عرض كل الإلهامات' : 'Voir toutes les inspirations' }}</span>
                <i class="las {{ $lang == 'ar' ? 'la-arrow-left' : 'la-arrow-right' }} fs-16"></i>
            </a>
        </div>

        <!-- Inspirations Slider / Tabs -->
        <div class="aiz-carousel home-inspirations-carousel gutters-16"
            data-items="1" data-arrows="true" data-dots="true" data-autoplay="false">
            @foreach($homeInspirations as $inspiration)
                @php
                    $title = $inspiration->getTitle($lang);
                    $subtitle = $inspiration->getSubtitle($lang);
                    $heroImage = $inspiration->hero_image ? Storage::disk('public')->url($inspiration->hero_image) : static_asset('assets/img/placeholder.jpg');
                @endphp
                <div class="carousel-box pb-3">
                    <div class="bg-white border rounded-lg overflow-hidden shadow-sm inspiration-card-container">
                        <div class="row no-gutters">
                            <!-- Left: Interactive Scene Image with Hotspots -->
                            <div class="col-lg-7 col-xl-8">
                                <div class="position-relative inspiration-scene-wrap">
                                    <a href="{{ route('inspirations.show', $inspiration->slug) }}" class="d-block overflow-hidden h-100">
                                        <img
                                            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                            data-src="{{ $heroImage }}"
                                            alt="{{ $title }}"
                                            class="img-fit lazyload w-100 h-100 inspiration-scene-img"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                                    <!-- Badges -->
                                    <div class="position-absolute" style="top: 16px; left: 16px; z-index: 10;">
                                        <span class="badge badge-inline badge-pill px-3 py-2 text-white font-weight-bold" style="background: rgba(31, 42, 58, 0.85); backdrop-filter: blur(8px); font-size: 12px;">
                                            <i class="las la-tags mr-1"></i> {{ $inspiration->items->count() }} {{ $lang == 'ar' ? 'منتجات' : 'articles' }}
                                        </span>
                                    </div>

                                    <!-- Hotspot Markers -->
                                    @foreach($inspiration->items as $item)
                                        @if($item->hotspot && $item->product)
                                            @php
                                                $prod = $item->product;
                                                $itemTitle = ($lang == 'ar' && $item->custom_title_ar) ? $item->custom_title_ar : (($lang != 'ar' && $item->custom_title_fr) ? $item->custom_title_fr : $prod->getTranslation('name', $lang));
                                                $prodImg = uploaded_asset($prod->thumbnail_img);
                                                $prodPrice = home_discounted_base_price($prod);
                                                $posX = $item->hotspot->x * 100;
                                                $posY = $item->hotspot->y * 100;
                                            @endphp
                                            <div class="web-hotspot-pin" style="left: {{ $posX }}%; top: {{ $posY }}%;">
                                                <button class="web-hotspot-btn" type="button" aria-label="{{ $itemTitle }}">
                                                    <span>{{ $loop->iteration }}</span>
                                                    <span class="hotspot-pulse-ring"></span>
                                                </button>
                                                <!-- Tooltip popover -->
                                                <div class="web-hotspot-tooltip">
                                                    <a href="{{ route('product', $prod->slug) }}" class="d-flex align-items-center text-dark text-decoration-none">
                                                        <img src="{{ $prodImg }}" alt="{{ $itemTitle }}" class="size-48px rounded object-fit-cover mr-2 flex-shrink-0" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                                        <div class="min-w-0 pr-1">
                                                            <div class="fs-12 fw-700 text-truncate-2 line-height-1-2 mb-1">{{ $itemTitle }}</div>
                                                            <div class="fs-12 fw-800 text-primary">{{ $prodPrice }}</div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Right: Details & Product Mini Grid -->
                            <div class="col-lg-5 col-xl-4 d-flex flex-column justify-content-between p-3 p-md-4 bg-light-subtle">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="background: #D6A24E; color: #fff !important; font-size: 11px;">
                                            {{ $lang == 'ar' ? 'تنسيق متناسق' : 'Shop The Look' }}
                                        </span>
                                        <span class="fs-12 text-secondary">
                                            {{ $inspiration->items->count() }} {{ $lang == 'ar' ? 'قطع مختارة' : 'pièces sélectionnées' }}
                                        </span>
                                    </div>

                                    <h3 class="fs-18 fs-md-22 fw-800 text-dark mb-1">
                                        <a href="{{ route('inspirations.show', $inspiration->slug) }}" class="text-dark hov-text-primary">
                                            {{ $title }}
                                        </a>
                                    </h3>
                                    @if($subtitle)
                                        <p class="fs-13 text-secondary mb-3">{{ $subtitle }}</p>
                                    @endif

                                    <!-- Product List for this scene -->
                                    <div class="inspiration-products-rail mb-3">
                                        <div class="row gutters-8">
                                            @foreach($inspiration->items->take(4) as $item)
                                                @if($item->product)
                                                    @php
                                                        $p = $item->product;
                                                        $pName = ($lang == 'ar' && $item->custom_title_ar) ? $item->custom_title_ar : (($lang != 'ar' && $item->custom_title_fr) ? $item->custom_title_fr : $p->getTranslation('name', $lang));
                                                    @endphp
                                                    <div class="col-6 mb-2">
                                                        <a href="{{ route('product', $p->slug) }}" class="d-flex align-items-center p-2 rounded bg-white border hov-shadow-sm text-decoration-none has-transition h-100">
                                                            <div class="position-relative size-40px flex-shrink-0 mr-2 rounded overflow-hidden">
                                                                <img src="{{ uploaded_asset($p->thumbnail_img) }}" alt="{{ $pName }}" class="w-100 h-100 object-fit-cover" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                                                <span class="position-absolute bg-dark text-white rounded-circle font-weight-bold d-flex align-items-center justify-content-center" style="bottom: 2px; right: 2px; width: 14px; height: 14px; font-size: 9px;">
                                                                    {{ $loop->iteration }}
                                                                </span>
                                                            </div>
                                                            <div class="min-w-0 flex-grow-1">
                                                                <div class="fs-11 fw-600 text-dark text-truncate">{{ $pName }}</div>
                                                                <div class="fs-11 fw-700 text-primary">{{ home_discounted_base_price($p) }}</div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <a href="{{ route('inspirations.show', $inspiration->slug) }}" class="btn btn-primary btn-block fw-700 py-2 fs-14 rounded-pill d-flex align-items-center justify-content-center gap-2">
                                        <span>{{ $lang == 'ar' ? 'استكشف هذه الغرفة بالكامل' : 'Explorer cette ambiance' }}</span>
                                        <i class="las {{ $lang == 'ar' ? 'la-arrow-left' : 'la-arrow-right' }}"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.inspiration-card-container {
    border: 1px solid rgba(226, 224, 214, 0.7) !important;
    border-radius: 16px !important;
    transition: all 0.3s ease;
}
.inspiration-scene-wrap {
    min-height: 380px;
    height: 100%;
    overflow: hidden;
    background: #1f2a3a;
}
.inspiration-scene-img {
    height: 420px;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.inspiration-card-container:hover .inspiration-scene-img {
    transform: scale(1.03);
}

/* Hotspot Pins */
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
@keyframes hotspotPulse {
    0% { transform: scale(0.9); opacity: 0.9; }
    50% { transform: scale(1.4); opacity: 0.2; }
    100% { transform: scale(1.6); opacity: 0; }
}

/* Tooltip Popover */
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
@media (max-width: 767px) {
    .inspiration-scene-img {
        height: 260px;
    }
    .web-hotspot-btn {
        width: 26px;
        height: 26px;
        font-size: 11px;
    }
}
</style>
@endif

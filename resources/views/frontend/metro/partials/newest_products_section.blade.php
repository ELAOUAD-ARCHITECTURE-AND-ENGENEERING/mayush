@php
    $lang = get_system_language()->code;
    $title = get_setting('metro_collections_newest_title', null, $lang) ?: translate('Nouvelles collections');
    $description = get_setting('metro_collections_newest_description', null, $lang) ?: translate('Découvrez une sélection exclusive de mobilier et décoration où design contemporain, confort et raffinement se rencontrent.');
    $cta = get_setting('metro_collections_newest_cta_text', null, $lang) ?: translate('View All');
    $link = get_setting('metro_collections_newest_cta_link', null, $lang) ?: route('search', ['sort_by' => 'newest']);
@endphp
<div class="metro-collection-subsection">
    <div class="metro-collection-copy text-white">
        <h2 class="metro-collection-title mb-0">{{ $title }}</h2>
        <p class="metro-collection-description mb-0">{{ $description }}</p>
        <a href="{{ $link }}" class="metro-collection-cta text-reset">{{ $cta }}</a>
        <div class="metro-collection-slider-nav">
            <button type="button" class="metro-collection-slider-arrow" onclick="clickToSlide('slick-prev','section_newest')" aria-label="{{ translate('Previous') }}">
                <i class="las la-angle-left"></i>
            </button>
            <button type="button" class="metro-collection-slider-arrow" onclick="clickToSlide('slick-next','section_newest')" aria-label="{{ translate('Next') }}">
                <i class="las la-angle-right"></i>
            </button>
        </div>
    </div>
    <div class="metro-collection-products aiz-carousel arrow-inactive-none" data-items="3" data-xxl-items="3" data-xl-items="3" data-lg-items="2" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows="false" data-infinite="true" data-autoplay="true">
        @forelse ($newest_products as $new_product)
            @include('frontend.metro.partials.collection_product_preview', ['product' => $new_product])
        @empty
            @include('frontend.metro.partials.collection_panel_placeholder_products')
        @endforelse
    </div>
</div>

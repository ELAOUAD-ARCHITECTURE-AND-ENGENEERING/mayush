@php
    $lang = get_system_language()->code;
    $best_selling_products = get_best_selling_products(12);
    $title = get_setting('metro_collections_best_selling_title', null, $lang) ?: translate("L’art de vivre commence chez vous");
    $description = get_setting('metro_collections_best_selling_description', null, $lang) ?: translate('Les meilleures ventes qui font la tendance cette saison.');
    $cta = get_setting('metro_collections_best_selling_cta_text', null, $lang) ?: translate('View All');
    $link = get_setting('metro_collections_best_selling_cta_link', null, $lang) ?: route('search', ['sort_by' => 'popular']);
@endphp
<div class="metro-collection-subsection">
    <div class="metro-collection-copy text-white">
        <h2 class="metro-collection-title mb-0">{{ $title }}</h2>
        <p class="metro-collection-description mb-0">{{ $description }}</p>
        <a href="{{ $link }}" class="metro-collection-cta text-reset">{{ $cta }}</a>
        <div class="metro-collection-slider-nav">
            <button type="button" class="metro-collection-slider-arrow" onclick="clickToSlide('slick-prev','section_best_selling')" aria-label="{{ translate('Previous') }}">
                <i class="las la-angle-left"></i>
            </button>
            <button type="button" class="metro-collection-slider-arrow" onclick="clickToSlide('slick-next','section_best_selling')" aria-label="{{ translate('Next') }}">
                <i class="las la-angle-right"></i>
            </button>
        </div>
    </div>
    <div class="metro-collection-products aiz-carousel arrow-inactive-none" data-items="3" data-xxl-items="3" data-xl-items="3" data-lg-items="2" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows="false" data-infinite="true" data-autoplay="true">
        @forelse ($best_selling_products as $product)
            @include('frontend.metro.partials.collection_product_preview', ['product' => $product])
        @empty
            @include('frontend.metro.partials.collection_panel_placeholder_products')
        @endforelse
    </div>
</div>

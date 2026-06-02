@php
    $productUrl = route('product', $product->slug);
    if ($product->auction_product == 1) {
        $productUrl = route('auction-product', $product->slug);
    }
@endphp
<div class="metro-collection-product-slide">
    <article class="metro-collection-product">
        <a href="{{ $productUrl }}" class="metro-collection-product-link text-reset">
            <img
                class="metro-collection-product-image lazyload"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ get_image($product->thumbnail, 'medium') }}"
                alt="{{ $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
            <span class="minw-0">
                <span class="metro-collection-product-name">{{ $product->getTranslation('name') }}</span>
                <span class="metro-collection-product-price">{{ home_discounted_base_price($product) }}</span>
            </span>
        </a>
    </article>
</div>

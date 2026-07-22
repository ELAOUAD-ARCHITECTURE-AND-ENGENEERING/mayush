@php
    $product_url = route('product', $product->slug);
    if ($product->auction_product == 1) {
        $product_url = route('auction-product', $product->slug);
    }
    $product_name = $product->getTranslation('name');
@endphp

<article class="order-confirmation-product-card aiz-card-box h-100 bg-white has-transition">
    <a href="{{ $product_url }}" class="order-confirmation-product-card__image">
        @if (discount_in_percentage($product) > 0)
            <span class="order-confirmation-product-card__badge">-{{ discount_in_percentage($product) }}%</span>
        @endif
        <img class="lazyload img-fit has-transition"
            src="{{ static_asset('assets/img/placeholder.jpg') }}"
            data-src="{{ uploaded_asset($product->thumbnail_img) }}"
            alt="{{ $product_name }}"
            title="{{ $product_name }}"
            loading="lazy"
            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
    </a>
    <div class="order-confirmation-product-card__body">
        <h3 class="order-confirmation-product-card__name">
            <a href="{{ $product_url }}" title="{{ $product_name }}">{{ $product_name }}</a>
        </h3>
        <div class="order-confirmation-product-card__price">
            @if ($product->auction_product == 0 && home_base_price($product) != home_discounted_base_price($product))
                <del class="order-confirmation-product-card__price-old">{{ home_base_price($product) }}</del>
            @endif
            <span class="order-confirmation-product-card__price-current">
                @if ($product->auction_product == 1)
                    {{ single_price($product->starting_bid) }}
                @else
                    {{ home_discounted_base_price($product) }}
                @endif
            </span>
        </div>
    </div>
</article>

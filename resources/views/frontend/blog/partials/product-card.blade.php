@php
    $productName = $product->getTranslation('name');
    $productUrl = route('product', $product->slug);
    $vendorName = optional(optional($product->user)->shop)->name ?: optional($product->user)->name;
    $image = uploaded_asset($product->thumbnail_img);
@endphp

<article class="mb-blog-product-card border rounded bg-white h-100 overflow-hidden">
    <a href="{{ $productUrl }}" class="d-block text-reset">
        <div class="h-160px overflow-hidden bg-light">
            <img
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ $image }}"
                alt="{{ $productName }}"
                class="img-fit lazyload h-100 w-100 has-transition"
                loading="lazy">
        </div>
        <div class="p-3">
            <span class="badge badge-soft-primary fs-11 fw-600 mb-2">{{ translate('Available on Mayush') }}</span>
            <h3 class="fs-14 fw-700 text-truncate-2 mb-2">{{ $productName }}</h3>
            @if($vendorName)
                <div class="fs-12 opacity-70 mb-2">{{ $vendorName }}</div>
            @endif
            <div class="d-flex align-items-center justify-content-between">
                <span class="fw-700 text-primary">{{ home_discounted_base_price($product) }}</span>
                <span class="fs-12 fw-700 text-primary">{{ translate('Shop on Mayush') }}</span>
            </div>
        </div>
    </a>
</article>

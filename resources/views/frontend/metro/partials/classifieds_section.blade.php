@php
    $promoted_products = get_promoted_products(20);
    $lang = get_system_language()->code;
@endphp

@if(count($promoted_products) > 0)
    <section class="mb-2 mb-md-3 mt-3 mt-md-5">
        <div class="container">
            <!-- Top Section -->
            <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between">
                <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">
                    <span class="">{{ translate('Promoted Ads') }}</span>
                </h3>
                <div class="d-flex">
                    <a class="text-blue fs-10 fs-md-12 fw-700 hov-text-primary animate-underline-primary" href="{{ route('customer.products') }}">{{ translate('View All') }}</a>
                </div>
            </div>
            <!-- Banner -->
            @php
                $classifiedBannerImage = get_setting('classified_banner_image', null, $lang);
                $classifiedBannerImageSmall = get_setting('classified_banner_image_small', null, $lang);
            @endphp
            @if ($classifiedBannerImage != null || $classifiedBannerImageSmall != null)
                <div class="mb-3 overflow-hidden hov-scale-img d-none d-md-block">
                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($classifiedBannerImage) }}" alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                </div>
                <div class="mb-3 overflow-hidden hov-scale-img d-md-none">
                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ $classifiedBannerImageSmall != null ? uploaded_asset($classifiedBannerImageSmall) : uploaded_asset($classifiedBannerImage) }}" alt="{{ env('APP_NAME') }} promo" class="lazyload img-fit h-100 has-transition" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                </div>
            @endif
            
            <!-- Slider Component -->
            @include('components.frontend.promotion_slider', ['products' => $promoted_products])
        </div>
    </section>
@endif

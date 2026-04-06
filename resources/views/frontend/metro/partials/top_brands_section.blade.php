@php
    $brands = get_all_brands();
@endphp

@if (count($brands) > 0)
<style>
    .metro-brand-box {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .metro-brand-box:hover {
        border-color: #67308f;
        box-shadow: 0 4px 15px rgba(103, 48, 143, 0.15);
        transform: translateY(-2px);
    }
    .metro-brand-box img {
        max-width: 100%;
        max-height: 80px;
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s ease;
    }
    .metro-brand-box:hover img {
        filter: grayscale(0%);
        opacity: 1;
    }
    .section-title-metro {
        font-size: 1.8rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 2rem;
        position: relative;
        padding-bottom: 0.75rem;
        color: #1e293b;
    }
    .section-title-metro::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: #67308f;
        border-radius: 2px;
    }
</style>

<section class="mb-5 mt-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="section-title-metro mb-0">{{ translate('Top Brands') }}</h3>
            <a href="{{ route('brands.all') }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-700 hov-shadow-lg has-transition">
                {{ translate('View All') }} <i class="las la-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="aiz-carousel gutters-16 arrow-dark arrow-inactive-none" 
            data-items="6" data-xl-items="6" data-lg-items="5" data-md-items="4" data-sm-items="3" data-xs-items="2" 
            data-arrows='true' data-autoplay='true' data-infinite='true' data-rows="2">
            @foreach ($brands as $brand)
                <div class="carousel-box mb-3">
                    <a href="{{ route('products.brand', $brand->slug) }}" class="d-block text-reset">
                        <div class="metro-brand-box">
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" 
                                data-src="{{ uploaded_asset($brand->logo) }}" 
                                alt="{{ $brand->getTranslation('name') }}" 
                                class="lazyload" 
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

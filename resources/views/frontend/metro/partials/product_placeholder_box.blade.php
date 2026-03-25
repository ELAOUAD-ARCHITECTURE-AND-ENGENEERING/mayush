<div class="aiz-card-box h-auto bg-white py-3 hov-scale-img opacity-60">
    <div class="position-relative h-140px h-md-200px img-fit overflow-hidden">
        <!-- Placeholder Image -->
        <a href="javascript:void(0)" class="d-block h-100 position-relative">
            <img
                class="mx-auto img-fit has-transition"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                alt="{{ translate('Sample Product') }}">
        </a>
        
        <!-- Sample Badge -->
        <span class="absolute-top-left rounded rounded-4 bg-soft-primary ml-1 mt-1 fs-11 fw-700 text-primary px-2">
            {{ translate('Sample') }}
        </span>

        <!-- Add to cart placeholder -->
        <a class="cart-btn absolute-bottom-left w-100 h-35px aiz-p-hov-icon text-white fs-13 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center"
            href="javascript:void(0)">
            <span class="cart-btn-text">
                {{ translate('Add to Cart') }}
            </span>
            <span><i class="las la-2x la-shopping-cart"></i></span>
        </a> 
    </div>

    <div class="p-2 p-md-3 text-left">
        <!-- Product name placeholder -->
        <h3 class="fw-400 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px text-center text-gray">
            {{ translate('Sample Product Title') }}
        </h3>
        <div class="fs-14 d-flex justify-content-center mt-3">
            <!-- price placeholder -->
            <div class="">
                <span class="fw-700 text-gray">{{ single_price(0) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="aiz-card-box rounded hov-shadow-md mt-1 mb-2 has-transition bg-white">
    @if(discount_in_percentage($product) > 0)
        <span class="badge-custom">{{ translate('OFF') }}<span class="box ml-1 mr-0">&nbsp;{{discount_in_percentage($product)}}%</span></span>
    @endif
    <div class="position-relative">
        @php
            $product_url = route('product', $product->slug);
            if($product->auction_product == 1) {
                $product_url = route('auction-product', $product->slug);
            }
        @endphp
        <a href="{{ $product_url }}" class="d-block text-center py-1">
            <img
                class="product-img-fit width-img lazyload mx-auto h-140px h-md-210px skeleton-shimmer"
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                data-src="{{ uploaded_asset($product->thumbnail_img, 'medium') }}"
                alt="{{  $product->getTranslation('name')  }}"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>
        @if ($product->wholesale_product)
            <span class="absolute-bottom-left fs-11 text-white fw-600 px-2 lh-1-8" style="background-color: #455a64">
                {{ translate('Wholesale') }}
            </span>
        @endif
        <div class="absolute-top-right aiz-p-hov-icon">
            <a href="javascript:void(0)" onclick="addToWishList({{ $product->id }})"  data-wenk="{{ translate('Add to wishlist') }}" data-wenk-pos="left">
                <i class="la la-heart-o"></i>
            </a>
            <a href="javascript:void(0)" onclick="addToCompare({{ $product->id }})"  data-wenk="{{ translate('Add to compare') }}" data-wenk-pos="left">
                <i class="las la-sync"></i>
            </a>
            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})"  data-wenk="{{ translate('Add to cart') }}" data-wenk-pos="left">
                <i class="las la-shopping-cart"></i>
            </a>
        </div>
    </div>
    <div class="p-md-3 p-2 text-left">
        <div class="fs-15">
            @if(home_base_price($product) != home_discounted_base_price($product))
                <del class="fw-600 opacity-50 mr-1">{{ home_base_price($product) }}</del>
            @endif
            <span class="fw-700 text-primary">{{ home_discounted_base_price($product) }}</span>
        </div>
        <div class="rating rating-sm mt-1">
            {{ renderStarRating($product->rating) }}
        </div>
        <h3 class="fw-600 fs-13 text-truncate-2 lh-1-4 mb-0 h-35px">
            <a href="{{ $product_url }}" class="d-block text-reset">{{  $product->getTranslation('name')  }}</a>
        </h3>
        @if (addon_is_activated('club_point'))
            @php
                $loyaltyPts = $product->earn_point;
                $ptsEquivalent = '';
                try {
                    $loyaltySvc = app(\App\Services\LoyaltyService::class);
                    $loyaltyPts = $loyaltySvc->getPotentialPoints($product, auth()->user());
                    $moneyVal = $loyaltySvc->pointsToMonetaryValue($loyaltyPts);
                    if ($moneyVal > 0) {
                        $ptsEquivalent = ' (' . single_price($moneyVal) . ')';
                    }
                } catch (\Exception $e) {}
            @endphp
            <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border d-flex justify-content-between align-items-center">
                <span class="fs-11">
                    <i class="las la-star text-warning"></i>
                    {{ translate('Earn') }} <span class="fw-700">{{ $loyaltyPts }}</span> {{ translate('pts') }}
                    <span class="text-muted fs-10">{{ $ptsEquivalent }}</span>
                </span>
            </div>
        @endif
    </div>
</div>


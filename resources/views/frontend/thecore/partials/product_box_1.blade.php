    $cart_added = [];
@endphp

@if(isset($modifier) && $modifier == 'modern')
    <div x-data="productCard()" 
         @mouseenter="hover = true" 
         @mouseleave="hover = false"
         class="flash-deal-card p-4 h-full bg-white group border border-transparent hover:border-red-500/10">
        
        <div class="relative aspect-square overflow-hidden rounded-xl mb-4">
            <a href="{{ $product_url }}" class="block h-full w-full">
                <img src="{{ get_image($product->thumbnail) }}" 
                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                     alt="{{ $product->getTranslation('name') }}">
            </a>
            
            @if (discount_in_percentage($product) > 0)
                <div class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-lg">
                    -{{ discount_in_percentage($product) }}%
                </div>
            @endif

            <!-- Quick Add Button -->
            <button @click="addToCart({{ $product->id }})" 
                    class="absolute bottom-2 right-2 p-3 bg-white/90 backdrop-blur rounded-full shadow-xl translate-y-12 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 hover:bg-gray-900 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <div class="text-center">
            <h3 class="text-sm font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-red-600 transition-colors">
                <a href="{{ $product_url }}">{{ $product->getTranslation('name') }}</a>
            </h3>
            
            <div class="flex flex-col items-center justify-center gap-1">
                @if (home_base_price($product) != home_discounted_base_price($product))
                    <span class="text-[11px] text-gray-400 line-through font-medium">{{ home_base_price($product) }}</span>
                @endif
                <span class="text-lg font-black text-gray-900">{{ home_discounted_base_price($product) }}</span>
            </div>
        </div>
    </div>
@else
<div class="aiz-card-box h-100 bg-white rounded-xl hov-shadow-lg has-transition border border-transparent overflow-hidden d-flex flex-column group" style="box-shadow: 0px 10px 30px rgba(0,0,0,0.05); border-radius: 12px; height: 100%;">
    <!-- Image Section -->
    <div class="position-relative w-100 overflow-hidden" style="aspect-ratio: 1/1;">
        @php
            $product_url = route('product', $product->slug);
            if ($product->auction_product == 1) {
                $product_url = route('auction-product', $product->slug);
            }
        @endphp
        <!-- Image -->
        <a href="{{ $product_url }}" class="d-block h-100 position-relative image-hover-effect overflow-hidden">
            <img
                class="lazyload w-100 h-100 has-transition product-main-image"
                style="object-fit: cover; transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);"
                src="{{ get_image($product->thumbnail) }}"
                alt="{{ $product->getTranslation('name') }}"
                title="{{ $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                onmouseover="this.style.transform='scale(1.05)'"
                onmouseout="this.style.transform='scale(1)'">
            <img
                class="lazyload w-100 h-100 has-transition product-hover-image position-absolute"
                style="object-fit: cover; top: 0; left: 0;"
                src="{{ get_first_product_image($product->photos, $product->thumbnail) }}"
                alt="{{ $product->getTranslation('name') }}"
                title="{{ $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
        </a>

        @php
            $badgeIndex = 0;
        @endphp
        <!-- Discount percentage tag -->
        @if (discount_in_percentage($product) > 0)
            <span class="absolute-top-left rounded fs-11 fw-700 text-white w-35px text-center shadow-sm"
                style="background-color: #F97316; padding: 4px 0; margin-top: 12px; margin-left: 12px; z-index: 2; top:{{ 30 * $badgeIndex }}px;">-{{ discount_in_percentage($product) }}%</span>
            @php $badgeIndex++; @endphp
        @endif
        <!-- Wholesale tag -->
        @if ($product->wholesale_product)
            <span class="absolute-top-left rounded fs-11 text-white fw-700 px-2 py-1 shadow-sm lh-1-8 ml-3 mt-3"
                style="background-color: #455a64; z-index: 2; @if (discount_in_percentage($product) > 0) top:{{ 30 * $badgeIndex }}px;"; @endif">
                {{ translate('Wholesale') }}
            </span>
            @php $badgeIndex++; @endphp
        @endif
         <!-- Custom Labels -->
        @php
            $customLabels = get_custom_labels($product->custom_label_id);
        @endphp
        @if ($customLabels)
            @foreach ($customLabels as $key => $customLabel)
                <span class="absolute-top-left rounded fs-11 fw-700 px-2 py-1 shadow-sm lh-1-8 ml-3 mt-3"
                    style="background-color:{{ $customLabel->background_color }};
                        color:{{ $customLabel->text_color }}; z-index: 2;
                        top:{{ 30 * $badgeIndex }}px;">
                    {{ $customLabel->text }}
                </span>
                @php $badgeIndex++; @endphp
            @endforeach
        @endif

        @if ($product->auction_product == 0)
            <!-- Desktop Icons (Top Right) -->
            <div class="d-none d-sm-block absolute-top-right aiz-p-hov-icon" style="z-index: 2; right: 12px; top: 12px;">
                <!-- Wishlist Icon -->
                <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm mb-2 hov-bg-primary hov-text-white has-transition d-flex align-items-center justify-content-center" onclick="addToWishList({{ $product->id }})"
                    data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left" style="width: 32px; height: 32px; border: none;">
                    <i class="la la-heart-o fs-16"></i>
                </a>

                <!-- Compare Icon -->
                <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm mb-2 hov-bg-primary hov-text-white has-transition d-flex align-items-center justify-content-center" onclick="addToCompare({{ $product->id }})"
                    data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left" style="width: 32px; height: 32px; border: none;">
                    <i class="las la-sync fs-16"></i>
                </a>
            </div>

            <!-- Mobile Icons (Bottom) -->
            <div class="d-sm-none position-absolute aiz-p-hov-icon-mobile"
                style="bottom: 10px; right: 10px; z-index: 10;">
                <div class="d-inline-flex flex-column">
                    <!-- Wishlist Icon -->
                    <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm mb-2 d-flex align-items-center justify-content-center"
                        onclick="addToWishList({{ $product->id }})" data-toggle="tooltip"
                        data-title="{{ translate('Add to wishlist') }}" data-placement="left" style="width: 32px; height: 32px; border: none;">
                        <i class="la la-heart-o fs-16 text-dark"></i>
                    </a>
                    
                    <!-- Compare Icon -->
                    <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm mb-2 d-flex align-items-center justify-content-center"
                        onclick="addToCompare({{ $product->id }})" data-toggle="tooltip"
                        data-title="{{ translate('Add to compare') }}" data-placement="left" style="width: 32px; height: 32px; border: none;">
                        <i class="las la-sync fs-16 text-dark"></i>
                    </a>

                    <!-- Cart Icon -->
                    <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                        onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip"
                        data-title="{{ translate('Add to Cart') }}" data-placement="left" style="width: 32px; height: 32px; border: none;">
                        <i class="las la-shopping-cart fs-16 text-dark"></i>
                    </a>
                </div>
            </div>

            <!-- Original Add to Cart (Desktop only) -->
            @php
                $colors = is_string($product->colors) ? json_decode($product->colors, true) : $product->colors;
                $attributes = is_string($product->attributes) ? json_decode($product->attributes, true) : $product->attributes;
            @endphp

            @if ( (is_array($colors) && count($colors) > 0) || (is_array($attributes) && count($attributes) > 0) )
                <a class="cart-btn absolute-bottom-left w-100 h-40px aiz-p-hov-icon text-white fs-14 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                    href="javascript:void(0)" onclick="showAddToCartRightCanvas({{ $product->id }})" style="background-color: rgba(30, 41, 59, 0.9); backdrop-filter: blur(4px);">
                    <span class="cart-btn-text">
                        {{ translate('Select Option') }}
                    </span>
                    <span><i class="las la-sliders-h" style="font-size: 1.4rem;"></i></span>
                </a>
            @else
                <a class="cart-btn absolute-bottom-left w-100 h-40px aiz-p-hov-icon text-white fs-14 fw-700 d-none d-sm-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                    href="javascript:void(0)" onclick="addToCartSingleProduct({{ $product->id }})" style="background-color: rgba(30, 41, 59, 0.9); backdrop-filter: blur(4px);">
                    <span class="cart-btn-text">
                        {{ translate('Add to Cart') }}
                    </span>
                    <span><i class="las la-shopping-cart" style="font-size: 1.4rem;"></i></span>
                </a> 
            @endif
        @endif

        @if (
            $product->auction_product == 1 &&
                $product->auction_start_date <= strtotime('now') &&
                $product->auction_end_date >= strtotime('now'))
            <!-- Place Bid -->
            @php
                $carts = get_user_cart();
                if (count($carts) > 0) {
                    $cart_added = $carts->pluck('product_id')->toArray();
                }
                $highest_bid = $product->bids->max('amount');
                $min_bid_amount = $highest_bid != null ? $highest_bid + 1 : $product->starting_bid;
                $gst_rate = gst_applicable_product_rate($product->id);
            @endphp
            <a class="cart-btn absolute-bottom-left w-100 h-40px aiz-p-hov-icon text-white fs-14 fw-700 d-flex flex-column justify-content-center align-items-center @if (in_array($product->id, $cart_added)) active @endif"
                href="javascript:void(0)" onclick="bid_single_modal({{ $product->id }}, {{ $min_bid_amount }}, {{ $gst_rate }})" style="background-color: rgba(30, 41, 59, 0.9); backdrop-filter: blur(4px);">
                <span class="cart-btn-text">{{ translate('Place Bid') }}</span>
                <span><i class="las la-2x la-gavel"></i></span>
            </a>
        @endif
    </div>

    <!-- Info Section -->
    <div class="p-3 text-center d-flex flex-column flex-grow-1 bg-white">
        <!-- Product name -->
        <h3 class="fw-700 fs-15 text-truncate-2 lh-1-5 mb-2" style="color: #1E293B;">
            <a href="{{ $product_url }}" class="d-block text-reset hov-text-primary"
                title="{{ $product->getTranslation('name') }}">{{ $product->getTranslation('name') }}</a>
        </h3>

        @php
            $total_stock = $product->stocks->sum('qty');
        @endphp
        @if($total_stock > 0 && $total_stock < 10)
            <div class="mb-2 text-center">
                <span class="badge badge-inline badge-soft-danger animate-pulse px-2 py-1">{{ translate('Only') }} {{ $total_stock }} {{ translate('left!') }}</span>
            </div>
        @elseif($total_stock > 0 && $product->num_of_sale > 100)
            <div class="mb-2 text-center">
                <span class="badge badge-inline badge-soft-warning px-2 py-1">{{ translate('Selling Fast!') }}</span>
            </div>
        @endif
        
        <div class="fs-16 d-flex justify-content-center align-items-end mt-auto pt-2">
            @if ($product->auction_product == 0)
                <!-- Previous price -->
                @if (home_base_price($product) != home_discounted_base_price($product))
                    <div class="disc-amount has-transition mr-2">
                        <del class="fw-500 text-secondary fs-13">{{ home_base_price($product) }}</del>
                    </div>
                @endif
                <!-- price -->
                <div class="">
                    <span class="fw-800 fs-16" style="color: #F97316;">{{ home_discounted_base_price($product) }}</span>
                </div>
            @endif
            @if ($product->auction_product == 1)
                <!-- Bid Amount -->
                <div class="">
                    <span class="fw-800 fs-16" style="color: #F97316;">{{ single_price($product->starting_bid) }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

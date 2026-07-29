@php
    $cart_added = [];
    $carts = get_user_cart();
    if ($carts && count($carts) > 0) {
        $cart_added = $carts->pluck('product_id')->toArray();
    }
@endphp
<div class="aiz-card-box h-auto border-0 bg-transparent hov-scale-img position-relative group" style="transition: all 0.3s ease;">
    <!-- Image Container with 4:3 Aspect Ratio -->
    <div class="position-relative overflow-hidden shadow-sm" style="border-radius: 14px; aspect-ratio: 4/3; background: #f8f9fa;">
        @php
            $product_url = route('product', $product->slug);
            if ($product->auction_product == 1) {
                $product_url = route('auction-product', $product->slug);
            }
        @endphp
        <!-- Image -->
        <a href="{{ $product_url }}" class="d-block w-100 h-100 position-relative image-hover-effect">
            <img class="lazyload w-100 h-100 product-main-image skeleton-shimmer"
                style="object-fit: cover; transition: transform 0.5s ease;"
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                data-src="{{ get_image($product->thumbnail, 'card') }}"
                alt="{{ \App\Services\SeoService::productAltText($product) }}"
                title="{{ $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                onmouseover="this.style.transform='scale(1.05)'"
                onmouseout="this.style.transform='scale(1)'">
            <img class="lazyload w-100 h-100 product-hover-image position-absolute skeleton-shimmer"
                style="top:0; left:0; object-fit: cover; opacity: 0; transition: opacity 0.5s ease, transform 0.5s ease;"
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                data-src="{{ get_first_product_image($product->photos, $product->thumbnail, 'card') }}"
                alt="{{ \App\Services\SeoService::productAltText($product, translate('Photo detail - Livraison Maroc')) }}"
                title="{{ $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                onmouseover="this.style.opacity='1'; this.style.transform='scale(1.05)'"
                onmouseout="this.style.opacity='0'; this.style.transform='scale(1)'">
        </a>
        
        <!-- Vertical Action Icons (Hidden by default, show on hover) -->
        <div class="position-absolute d-flex flex-column align-items-center" 
             style="top: 10px; right: 10px; z-index: 10; gap: 8px; opacity: 0; visibility: hidden; transform: translateX(10px); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);"
             onmouseover="this.style.opacity='1'; this.style.visibility='visible'; this.style.transform='translateX(0)';"
             onmouseout="this.parentElement.onmouseover ? null : (this.style.opacity='0', this.style.visibility='hidden', this.style.transform='translateX(10px)')">
            <!-- Wishlist -->
            <button type="button" class="btn btn-icon btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                onclick="addToWishList({{ $product->id }})" 
                data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left" 
                style="width: 32px; height: 32px; border: none; background: rgba(255, 255, 255, 0.9); color: #1E293B; transition: all 0.2s; padding: 0;"
                onmouseover="this.style.background='#F97316'; this.style.color='#fff';"
                onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='#1E293B';">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </button>
            
            <!-- Compare -->
            <button type="button" class="btn btn-icon btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                onclick="addToCompare({{ $product->id }})" 
                data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left" 
                style="width: 32px; height: 32px; border: none; background: rgba(255, 255, 255, 0.9); color: #1E293B; transition: all 0.2s; padding: 0;"
                onmouseover="this.style.background='#F97316'; this.style.color='#fff';"
                onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='#1E293B';">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            </button>
            
            <!-- Cart / Options -->
            @if ($product->auction_product == 0)
                @php
                    $colors = is_string($product->colors) ? json_decode($product->colors, true) : $product->colors;
                    $attributes = is_string($product->attributes) ? json_decode($product->attributes, true) : $product->attributes;
                @endphp
                @if ( (is_array($colors) && count($colors) > 0) || (is_array($attributes) && count($attributes) > 0) )
                    <button type="button" class="btn btn-icon btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                        onclick="showAddToCartRightCanvas({{ $product->id }})" 
                        data-toggle="tooltip" data-title="{{ translate('Select Option') }}" data-placement="left" 
                        style="width: 32px; height: 32px; border: none; background: rgba(255, 255, 255, 0.9); color: #1E293B; transition: all 0.2s; padding: 0;"
                        onmouseover="this.style.background='#F97316'; this.style.color='#fff';"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='#1E293B';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                    </button>
                @else
                    <button type="button" class="btn btn-icon btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                        onclick="addToCartSingleProduct({{ $product->id }})" 
                        data-toggle="tooltip" data-title="{{ translate('Add to Cart') }}" data-placement="left" 
                        style="width: 32px; height: 32px; border: none; background: rgba(255, 255, 255, 0.9); color: #1E293B; transition: all 0.2s; padding: 0;"
                        onmouseover="this.style.background='#F97316'; this.style.color='#fff';"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.color='#1E293B';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </button>
                @endif
            @endif
        </div>
        
        <!-- JavaScript to handle showing icons on parent hover -->
        <script>
            document.currentScript.parentElement.onmouseover = function() {
                var icons = this.querySelector('.position-absolute.d-flex.flex-column');
                if (icons) {
                    icons.style.opacity = '1';
                    icons.style.visibility = 'visible';
                    icons.style.transform = 'translateX(0)';
                }
            };
            document.currentScript.parentElement.onmouseout = function() {
                var icons = this.querySelector('.position-absolute.d-flex.flex-column');
                if (icons) {
                    icons.style.opacity = '0';
                    icons.style.visibility = 'hidden';
                    icons.style.transform = 'translateX(10px)';
                }
            };
        </script>

        <!-- Discount Badge -->
        @if (discount_in_percentage($product) > 0)
            <span class="position-absolute rounded shadow-sm fw-700 text-white"
                style="top: 10px; left: 10px; background-color: #F97316; padding: 4px 8px; font-size: 11px; z-index: 10;">
                -{{ discount_in_percentage($product) }}%
            </span>
        @endif
    </div>

    <!-- Product Info (Centered below image) -->
    <div class="pt-3 pb-2 text-center">
        <!-- Product name -->
        <h3 class="fw-600 fs-14 mb-1 lh-1-4">
            <a href="{{ $product_url }}" class="d-block text-reset text-decoration-none"
               style="color: #1E293B; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.2s;"
               onmouseover="this.style.color='#F97316'" onmouseout="this.style.color='#1E293B'"
               title="{{ $product->getTranslation('name') }}">
                {{ $product->getTranslation('name') }}
            </a>
        </h3>
        
        <!-- Price -->
        <div class="fs-15 d-flex justify-content-center align-items-center mt-2">
            @if ($product->auction_product == 0)
                @if (home_base_price($product) != home_discounted_base_price($product))
                    <del class="fw-500 mr-2" style="color: #94A3B8; font-size: 13px;">{{ home_base_price($product) }}</del>
                @endif
                <span class="fw-800" style="color: #F97316;">{{ home_discounted_base_price($product) }}</span>
            @endif
            @if ($product->auction_product == 1)
                <span class="fw-800" style="color: #F97316;">{{ single_price($product->starting_bid) }}</span>
            @endif
        </div>
    </div>
</div>

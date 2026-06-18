@php
    $productUrl = route('product', $product->slug);
    if ($product->auction_product == 1) {
        $productUrl = route('auction-product', $product->slug);
    }
    $cart_added = [];
    $carts = get_user_cart();
    if ($carts && count($carts) > 0) {
        $cart_added = $carts->pluck('product_id')->toArray();
    }
@endphp
<div class="metro-collection-product-slide" style="padding: 10px 15px 25px;">
    <article class="metro-collection-product d-flex flex-column position-relative group mx-auto" style="max-width: 170px; border-radius: 10px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.12); transition: transform 0.3s ease, border-color 0.3s ease;">
        <!-- Image Container -->
        <div class="position-relative w-100 overflow-hidden" style="aspect-ratio: 1/1;">
            <a href="{{ $productUrl }}" class="d-block w-100 h-100 position-relative image-hover-effect overflow-hidden">
                <img class="lazyload w-100 h-100"
                    style="object-fit: cover; transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);"
                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                    data-src="{{ get_image($product->thumbnail, 'medium') }}"
                    alt="{{ $product->getTranslation('name') }}"
                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                    onmouseover="this.style.transform='scale(1.06)'; this.parentNode.parentNode.parentNode.style.borderColor='rgba(214, 162, 78, 0.6)'"
                    onmouseout="this.style.transform='scale(1)'; this.parentNode.parentNode.parentNode.style.borderColor='rgba(255, 255, 255, 0.1)'">
            </a>
            
            <!-- Floating Icons (Wishlist & Cart) inside the image -->
            <div class="position-absolute d-flex flex-column" style="top: 6px; right: 6px; z-index: 10; gap: 6px;">
                <button type="button" class="btn btn-icon btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                    onclick="addToWishList({{ $product->id }})" 
                    data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left" 
                    style="width: 28px; height: 28px; border: none; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); color: #1E293B; transition: all 0.2s; padding: 0;"
                    onmouseover="this.style.background='#F97316'; this.style.color='#fff';"
                    onmouseout="this.style.background='rgba(255, 255, 255, 0.85)'; this.style.color='#1E293B';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </button>
                @if ($product->auction_product == 0)
                    @php
                        $colors = is_string($product->colors) ? json_decode($product->colors, true) : $product->colors;
                        $attributes = is_string($product->attributes) ? json_decode($product->attributes, true) : $product->attributes;
                    @endphp
                    @if ( (is_array($colors) && count($colors) > 0) || (is_array($attributes) && count($attributes) > 0) )
                        <button type="button" class="btn btn-icon btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                            onclick="showAddToCartRightCanvas({{ $product->id }})" 
                            data-toggle="tooltip" data-title="{{ translate('Select Option') }}" data-placement="left" 
                            style="width: 28px; height: 28px; border: none; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); color: #1E293B; transition: all 0.2s; padding: 0;"
                            onmouseover="this.style.background='#F97316'; this.style.color='#fff';"
                            onmouseout="this.style.background='rgba(255, 255, 255, 0.85)'; this.style.color='#1E293B';">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line>
                                <line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line>
                                <line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line>
                                <line x1="17" y1="16" x2="23" y2="16"></line>
                            </svg>
                        </button>
                    @else
                        <button type="button" class="btn btn-icon btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                            onclick="addToCartSingleProduct({{ $product->id }})" 
                            data-toggle="tooltip" data-title="{{ translate('Add to Cart') }}" data-placement="left" 
                            style="width: 28px; height: 28px; border: none; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); color: #1E293B; transition: all 0.2s; padding: 0;"
                            onmouseover="this.style.background='#F97316'; this.style.color='#fff';"
                            onmouseout="this.style.background='rgba(255, 255, 255, 0.85)'; this.style.color='#1E293B';">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </button>
                    @endif
                @endif
            </div>
            
            <!-- Discount Badge -->
            @if (discount_in_percentage($product) > 0)
                <span class="position-absolute rounded fw-700 text-white shadow-sm"
                    style="top: 6px; left: 6px; background-color: #F97316; padding: 2px 5px; font-size: 10px; z-index: 10;">
                    -{{ discount_in_percentage($product) }}%
                </span>
            @endif
        </div>

        <!-- Info Container -->
        <div class="p-2 d-flex flex-column justify-content-center text-center w-100" style="background: rgba(18, 25, 42, 0.5);">
            <a href="{{ $productUrl }}" class="d-block text-reset text-decoration-none hov-text-primary">
                <h3 class="text-white mb-1" style="font-size: 12px; font-weight: 500; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 32px; line-height: 1.35;">
                    {{ $product->getTranslation('name') }}
                </h3>
            </a>
            <div class="d-flex justify-content-center align-items-end mt-1">
                @if ($product->auction_product == 0)
                    @if (home_base_price($product) != home_discounted_base_price($product))
                        <del class="fw-500 mr-1" style="color: rgba(255,255,255,0.6); font-size: 11px;">{{ home_base_price($product) }}</del>
                    @endif
                    <span class="fw-800" style="color: #F97316; font-size: 13px;">{{ home_discounted_base_price($product) }}</span>
                @else
                    <span class="fw-800" style="color: #F97316; font-size: 13px;">{{ single_price($product->starting_bid) }}</span>
                @endif
            </div>
        </div>
    </article>
</div>

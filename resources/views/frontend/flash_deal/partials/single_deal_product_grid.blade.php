{{-- Individual Deal Product Grid Partial – used by full page load AND AJAX refresh --}}
@foreach($flash_deal->flash_deal_products as $fd_product)
    @php
        $product = $fd_product->product;
        if(!$product || $product->published == 0) continue;
        
        $discount = 0;
        if($product->unit_price > 0){
            $discount = $product->discount;
        }

        // Stock calculation via service
        $stockData = \App\Services\FlashDealStockService::getStockData($product);
        $remaining_pct = $stockData['percentage'];
        $stock_color   = $stockData['color'];
        $stock_label   = $stockData['text'];
    @endphp
    <div class="product-card fade-in" onclick="window.location='{{ route('product', $product->slug) }}'">
        <div class="card-img-wrap">
          <img src="{{ uploaded_asset($product->thumbnail_img) }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">

          <div class="mdn-badge-wrap">
            @if($product->discount > 0)
              <span class="mdn-badge mdn-badge-pct">-{{ $product->discount }}%</span>
            @endif
            <span class="mdn-badge mdn-badge-hot">🔥 {{ translate('Populaire') }}</span>
          </div>

          <button class="wishlist-btn" onclick="event.stopPropagation(); addToWishList({{ $product->id }})">♡</button>

          <div class="mini-timer" data-end="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}">
            <div class="mt-block"><span class="mt-num mt-h">00</span><span class="mt-lbl">HRS</span></div>
            <span class="mt-sep">:</span>
            <div class="mt-block"><span class="mt-num mt-m">00</span><span class="mt-lbl">MIN</span></div>
            <span class="mt-sep">:</span>
            <div class="mt-block"><span class="mt-num mt-s">00</span><span class="mt-lbl">SEC</span></div>
          </div>
        </div>

        <div class="card-body">
          <div class="vendor-row">
            <div class="vendor">
              <div class="v-dot" style="background:var(--amber)">{{ substr($product->user->name ?? 'M', 0, 1) }}</div>
              <span class="v-name">{{ $product->user->name ?? translate('Mayush Seller') }}</span>
              <span class="v-check">✓</span>
            </div>
            <div class="stars">
              @php
                  $rating = round($product->rating);
                  $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                  $review_count = $product->reviews->count();
              @endphp
              <span class="s">{{ $stars }}</span>
              <span class="n">({{ $review_count }})</span>
            </div>
          </div>

          <div class="card-name">{{ $product->getTranslation('name') }}</div>
          <div class="card-desc">{{ $product->getTranslation('meta_description') }}</div>

@php
    $base_price_val = home_base_price($product, false);
    $discounted_price_val = home_discounted_base_price($product, false);
    if ($discounted_price_val >= $base_price_val && isset($fd_product) && $fd_product->discount > 0) {
        if ($fd_product->discount_type == 'percent') {
            $discounted_price_val = $base_price_val - ($base_price_val * $fd_product->discount) / 100;
        } elseif ($fd_product->discount_type == 'amount') {
            $discounted_price_val = $base_price_val - convert_price($fd_product->discount);
        }
    }
    $base_price_fmt = format_price($base_price_val);
    $discounted_price_fmt = format_price($discounted_price_val);
    $active_discount_pct = 0;
    if ($base_price_val > 0 && $discounted_price_val < $base_price_val) {
        $active_discount_pct = round((($base_price_val - $discounted_price_val) / $base_price_val) * 100);
    }
@endphp
          <div class="price-row d-flex align-items-center" style="gap: 8px; margin-bottom: 8px;">
            <span class="curr-prc" style="color: #ff4500; font-size: 24px; font-weight: 900; letter-spacing: -0.5px; line-height: 1.2;">{{ $discounted_price_fmt }}</span>
            <span class="old-prc" style="font-size: 14px; color: #888; font-weight: 500; text-decoration: line-through;">{{ $base_price_fmt }}</span>
            @if($active_discount_pct > 0)
              <span class="p-save" style="background-color: #e8f5e9; color: #00c853; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; display: inline-block;">{{ translate('Save') }} {{ $active_discount_pct }}%</span>
            @endif
          </div>

          <div class="velocity">
            <span>🔥</span>
            <div class="vel-bar"><div class="vel-fill" style="width: {{ $remaining_pct }}%; background: {{ $stock_color }};"></div></div>
            <span class="vel-count">{{ $stock_label }}</span>
          </div>

          <div class="card-actions">
            <button class="btn-primary" onclick="event.stopPropagation(); showAddToCartModal({{ $product->id }})">{{ translate('Au Panier') }}</button>
            <button class="btn-secondary" onclick="event.stopPropagation(); window.location='{{ route('product', $product->slug) }}'">
              {{ translate('Détails') }} ↗
            </button>
          </div>
        </div>
    </div>
@endforeach

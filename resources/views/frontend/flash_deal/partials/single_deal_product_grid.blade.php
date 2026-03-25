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

          <div class="price-row">
            <span class="curr-prc">{{ home_discounted_base_price($product) }}</span>
            <span class="old-prc">{{ home_base_price($product) }}</span>
            @if($product->discount > 0)
                <span class="p-save">{{ translate('Save') }} {{ $product->discount }}%</span>
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

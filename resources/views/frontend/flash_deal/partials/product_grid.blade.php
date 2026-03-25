{{-- Product Grid Partial – used by full page load AND AJAX refresh --}}
@foreach($all_flash_deals as $deal)
    @foreach($deal->flash_deal_products as $fd_product)
        @php
            $product = $fd_product->product;
            if(!$product) continue;
            $discount = 0;
            if($product->unit_price > 0){
                $discount = $product->discount;
            }
            $isFeatured = $loop->parent->first && $loop->first;

            // Category mapping
            $cat_slug = 'other';
            $cat_name = strtolower($product->category ? $product->category->getTranslation('name') : '');
            if(Str::contains($cat_name, ['meuble', 'table', 'chaise', 'canapé', 'lit', 'furniture'])) $cat_slug = 'furniture';
            elseif(Str::contains($cat_name, ['luminaire', 'lampe', 'éclairage', 'lighting'])) $cat_slug = 'lighting';
            elseif(Str::contains($cat_name, ['murale', 'tableau', 'miroir', 'wall art'])) $cat_slug = 'wall-art';
            elseif(Str::contains($cat_name, ['plantes', 'cache-pot', 'plants'])) $cat_slug = 'plants';
            elseif(Str::contains($cat_name, ['tapis', 'coussin', 'rideau', 'textiles'])) $cat_slug = 'textiles';
            elseif(Str::contains($cat_name, ['artisanat', 'fait main', 'craft'])) $cat_slug = 'craft';

            $price_num = home_discounted_base_price($product, false);
            $discount_num = $discount;
            $sales_num = $product->num_of_sale;

            // Stock calculation via service
            $stockData = \App\Services\FlashDealStockService::getStockData($product);
            $remaining_pct = $stockData['percentage'];
            $stock_color   = $stockData['color'];
            $stock_label   = $stockData['text'];
        @endphp
        <div class="product-card @if($isFeatured) featured @endif fade-in"
             data-category="{{ $cat_slug }}"
             data-price="{{ $price_num }}"
             data-discount="{{ $discount_num }}"
             data-sales="{{ $sales_num }}"
             onclick="window.location='{{ route('product', $product->slug) }}'">
            @if($product->featured)
                <div class="rank-badge">#{{ $loop->iteration }} {{ translate('Best Seller') }}</div>
            @endif

            <div class="card-img-wrap">
              <img src="{{ uploaded_asset($product->thumbnail_img) }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">

              <div class="deal-ribbon" style="background:var(--fire)" onclick="event.stopPropagation(); window.location='{{ route('flash-deal-details', $deal->slug) }}'">
                {{ $deal->title }}
              </div>

              <div class="mdn-badge-wrap">
                @if($product->discount > 0)
                  <span class="mdn-badge mdn-badge-pct">-{{ $product->discount }}%</span>
                @endif
                <span class="mdn-badge mdn-badge-hot">🔥 {{ translate('High Demand') }}</span>
              </div>

              <button class="wishlist-btn" onclick="event.stopPropagation(); addToWishList({{ $product->id }})">♡</button>

              <div class="mini-timer" data-end="{{ date('Y/m/d H:i:s', $deal->end_date) }}">
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

              <div class="card-name">{{ $product->name }}</div>
              <div class="card-desc">{{ $product->meta_description }}</div>

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
                <button class="btn-primary" onclick="event.stopPropagation(); showAddToCartModal({{ $product->id }})">{{ translate('Add to Cart') }}</button>
                <button class="btn-secondary" onclick="event.stopPropagation(); window.location='{{ route('flash-deal-details', $deal->slug) }}'">
                  {{ translate('View Deal') }} ↗
                </button>
              </div>
            </div>
        </div>
    @endforeach
@endforeach

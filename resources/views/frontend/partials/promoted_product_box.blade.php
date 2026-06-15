<div class="product-card promoted-card tier-{{ $product->promotion->tier ?? 'standard' }} h-100">
    <div class="card-img-wrap">
        @php
            $product_url = route('customer.product', $product->slug);
        @endphp
        <a href="{{ $product_url }}" class="d-block h-100">
            <img
                class="img-fit lazyload"
                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                data-src="{{ uploaded_asset($product->thumbnail_img, 'card') }}"
                width="480"
                height="480"
                loading="lazy"
                decoding="async"
                alt="{{ $product->getTranslation('name') }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
            >
        </a>
        
        {{-- Tier Badge --}}
        <div class="mdn-badge-wrap">
            @if(isset($product->promotion))
                @if($product->promotion->tier == 'gold')
                    <span class="mdn-badge mdn-badge-gold">
                        <i class="las la-crown mr-1"></i>{{ translate('GOLD') }}
                    </span>
                @elseif($product->promotion->tier == 'premium')
                    <span class="mdn-badge mdn-badge-premium">
                        <i class="las la-star mr-1"></i>{{ translate('PREMIUM') }}
                    </span>
                @else
                    <span class="mdn-badge mdn-badge-standard">
                        <i class="las la-check-circle mr-1"></i>{{ translate('PROMOTED') }}
                    </span>
                @endif
            @endif
        </div>

        {{-- Condition Badge --}}
        <div class="absolute-top-left pt-2 pl-2">
            @if($product->conditon == 'new')
               <span class="badge badge-inline badge-success">{{translate('New')}}</span>
            @elseif($product->conditon == 'used')
               <span class="badge badge-inline badge-danger">{{translate('Used')}}</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="price-row mb-1">
            <span class="p-now fw-700 text-primary">{{ single_price($product->unit_price) }}</span>
        </div>
        
        <h3 class="card-name fs-14 fw-600 mb-2">
            <a href="{{ $product_url }}" class="text-reset text-truncate-2" title="{{ $product->getTranslation('name') }}">
                {{ $product->getTranslation('name') }}
            </a>
        </h3>

        <div class="vendor-row mt-auto">
            <div class="vendor">
                <span class="v-name"><i class="las la-map-marker mr-1"></i>{{ $product->location }}</span>
            </div>
        </div>
    </div>
</div>

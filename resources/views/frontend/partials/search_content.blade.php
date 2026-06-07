<div class="">
    @if (sizeof($keywords) > 0)
        <div class="px-2 py-1 text-uppercase fs-10 text-right text-muted bg-soft-secondary">{{translate('Popular Suggestions')}}</div>
        <ul class="list-group list-group-raw">
            @foreach ($keywords as $key => $keyword)
                <li class="list-group-item py-1">
                    <a class="text-dark hov-text-primary" href="{{ route('suggestion.search', $keyword) }}">{{ $keyword }}</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
<div class="">
    @if (count($categories) > 0)
        <div class="px-2 py-1 text-uppercase fs-10 text-right text-muted bg-soft-secondary">{{translate('Category Suggestions')}}</div>
        <ul class="list-group list-group-raw">
            @foreach ($categories as $key => $category)
                <li class="list-group-item py-1">
                    <a class="text-dark hov-text-primary" href="{{ route('products.category', $category->slug) }}">{{ $category->getTranslation('name') }}</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
<div class="">
    @if ( count($products) > 0)
        @if(isset($is_ai_mode) && $is_ai_mode)
            <div class="px-2 py-2 text-uppercase fs-11 fw-700 text-white bg-primary d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, var(--primary), #6366f1) !important;">
                <span>✨ {{translate('AI Design Suggestions')}}</span>
                <span class="fs-10 opacity-70">{{ translate('Aesthetic Match') }}</span>
            </div>
        @else
            <div class="px-2 py-1 text-uppercase fs-10 text-right text-muted bg-soft-secondary">{{translate('Products')}}</div>
        @endif
        <ul class="list-group list-group-raw">
            @foreach ($products as $key => $product)
                @php
                    $score = isset($semantic_scores) ? ($semantic_scores[$product->id] ?? null) : null;
                @endphp
                <li class="list-group-item">
                    <a class="text-dark" href="{{ route('product', $product->slug) }}">
                        <div class="d-flex search-product align-items-center">
                            @if($score)
                                <div class="ai-match-badge position-absolute top-0 right-0 mt-2 mr-2">
                                    <span class="badge badge-inline badge-pill badge-primary fs-10" style="background: #6366f1;">{{ round($score * 100) }}% Match</span>
                                </div>
                            @endif
                            <div class="mr-3">
                                <img class="size-40px img-fit rounded" src="{{ uploaded_asset($product->thumbnail_img) }}">
                            </div>
                            <div class="flex-grow-1 overflow--hidden minw-0">
                                <div class="product-name text-truncate fs-14 mb-5px">
                                    {{  $product->getTranslation('name')  }}
                                </div>
                                <div class="">
                                    @if(home_base_price($product) != home_discounted_base_price($product))
                                        <del class="opacity-60 fs-15">{{ home_base_price($product) }}</del>
                                    @endif
                                    <span class="fw-600 fs-16 text-primary">{{ home_discounted_base_price($product) }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<div class="">
    @if (addon_is_activated('preorder') && count($preorder_products) > 0)
        <div class="px-2 py-1 text-uppercase fs-10 text-right text-muted bg-soft-secondary">{{translate('Preorder Products')}}</div>
        <ul class="list-group list-group-raw">
            @foreach ($preorder_products as $key => $product)
                <li class="list-group-item">
                    <a class="text-dark" href="{{ route('preorder-product.details', $product->product_slug) }}">
                        <div class="d-flex search-product align-items-center">
                            <div class="mr-3">
                                <img class="size-40px img-fit rounded" src="{{ uploaded_asset($product->thumbnail) }}">
                            </div>
                            <div class="flex-grow-1 overflow--hidden minw-0">
                                <div class="product-name text-truncate fs-14 mb-5px">
                                    {{  $product->product_name  }}
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>


@if(get_setting('vendor_system_activation') == 1)
    <div class="">
        @if (count($shops) > 0)
            <div class="px-2 py-1 text-uppercase fs-10 text-right text-muted bg-soft-secondary">{{translate('Shops')}}</div>
            <ul class="list-group list-group-raw">
                @foreach ($shops as $key => $shop)
                    <li class="list-group-item">
                        <a class="text-dark" href="{{ route('shop.visit', $shop->slug) }}">
                            <div class="d-flex search-product align-items-center">
                                <div class="mr-3">
                                    <img class="size-40px img-fit rounded" src="{{ uploaded_asset($shop->logo) }}">
                                </div>
                                <div class="flex-grow-1 overflow--hidden">
                                    <div class="product-name text-truncate fs-14 mb-5px">
                                        {{ $shop->name }}
                                    </div>
                                    <div class="opacity-60">
                                        {{ $shop->address }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

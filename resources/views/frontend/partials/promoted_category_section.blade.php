@if(get_setting('promoted_category_status') == '1' && get_setting('promoted_category_id'))
    @php
        $promoted_category_id = get_setting('promoted_category_id');
        $promoted_category = $promoted_category_id ? \App\Models\Category::find($promoted_category_id) : null;
        $promoted_products = collect();
        
        if($promoted_category) {
            // Safer way to get child IDs - using the 'categories' relationship defined in the model
            $category_ids = array_merge([$promoted_category->id], $promoted_category->categories->pluck('id')->toArray());
            
            // Primary query: Discounted products
            $promoted_products = \App\Models\Product::whereIn('category_id', $category_ids)
                ->where('published', 1)
                ->where('approved', 1)
                ->where('discount', '>', 0)
                ->latest()
                ->limit(12)
                ->get();
            
            // Fallback: If no discounts, show latest products in this category
            if($promoted_products->count() == 0) {
                $promoted_products = \App\Models\Product::whereIn('category_id', $category_ids)
                    ->where('published', 1)
                    ->where('approved', 1)
                    ->latest()
                    ->limit(12)
                    ->get();
            }
        }
    @endphp

    @if($promoted_products->count() > 0)
    <section class="promoted-category-section mb-4">
        <div class="container">
            <div class="promoted-category-card shadow-sm rounded overflow-hidden">
                {{-- Section Header --}}
                <div class="promoted-header d-flex align-items-center justify-content-between px-4 py-3">
                    <div class="d-flex align-items-center">
                        <div class="promoted-badge mr-3">
                            <i class="las la-fire pulse"></i>
                        </div>
                        <div>
                            <h4 class="fs-18 fw-700 mb-0 text-dark">{{ $promoted_category->getTranslation('name') }}</h4>
                            <span class="fs-12 text-muted">{{ translate('Special promotions on selected products') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('products.category', $promoted_category->slug) }}" class="btn btn-sm btn-outline-primary hov-svg-white rounded-pill px-4">
                        {{ translate('View All') }} <i class="las la-arrow-right ml-1"></i>
                    </a>
                </div>

                {{-- Product Grid --}}
                <div class="px-3 pb-3 pt-2 bg-white">
                    <div class="aiz-carousel gutters-10 arrow-dark arrow-inactive-none" data-items="6" data-xxl-items="6" data-xl-items="5" data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-dots='false' data-infinite='true' data-autoplay='true'>
                        @foreach($promoted_products as $key => $product)
                            <div class="carousel-box px-1">
                                @include('frontend.metro.partials.product_box_2', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
    .promoted-category-card {
        background: #fff;
        border: 1px solid #eef2f7;
        transition: all 0.3s ease;
    }
    .promoted-category-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    .promoted-header {
        background: linear-gradient(135deg, #fffcf9 0%, #fff3e0 100%);
        border-bottom: 1px solid #ffe0b2;
    }
    .promoted-badge {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #f97316, #ef4444);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }
    .pulse {
        animation: pulse-red 2s infinite;
    }
    @keyframes pulse-red {
        0% { transform: scale(0.95); }
        70% { transform: scale(1.05); }
        100% { transform: scale(0.95); }
    }
    </style>
    @endif
@endif

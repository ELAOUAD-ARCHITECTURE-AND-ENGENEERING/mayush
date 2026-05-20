@if(get_setting('promoted_category_status') == '1' && get_setting('promoted_category_id'))
    @php
        $lang = get_system_language()->code;
        $promoted_category_id = get_setting('promoted_category_id');
        $promoted_category = $promoted_category_id ? \App\Models\Category::find($promoted_category_id) : null;
        $promoted_products = collect();
        $promoted_category_subtitle = trim((string) get_setting('promoted_category_subtitle', '', $lang));
        $promoted_category_subtitle = $promoted_category_subtitle !== ''
            ? $promoted_category_subtitle
            : translate('Des espaces inspirants pour plus d’efficacité Découvrez notre sélection exclusive de mobilier de bureau alliant design, confort et fonctionnalité.');
        
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
                <div class="promoted-header d-flex align-items-start justify-content-between px-4 py-3">
                    <div class="promoted-heading d-flex align-items-start">
                        <div class="promoted-badge mr-3">
                            <i class="las la-fire pulse"></i>
                        </div>
                        <div class="promoted-heading-copy">
                            <h2 class="promoted-category-title fs-18 fw-700 mb-1 text-dark">{{ $promoted_category->getTranslation('name') }}</h2>
                            @if ($promoted_category_subtitle)
                                <h3 class="promoted-category-subtitle fs-14 fw-500 text-muted mb-0">{{ $promoted_category_subtitle }}</h3>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('products.category', $promoted_category->slug) }}" class="promoted-view-all btn btn-sm btn-outline-primary hov-svg-white rounded-pill px-4">
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
        gap: 18px;
    }
    .promoted-category-title,
    .promoted-category-subtitle {
        letter-spacing: 0;
        line-height: 1.35;
    }
    .promoted-heading {
        min-width: 0;
        flex: 1 1 auto;
    }
    .promoted-heading-copy {
        min-width: 0;
        max-width: 820px;
    }
    .promoted-category-subtitle {
        display: block;
        max-width: 820px;
    }
    .promoted-view-all {
        flex: 0 0 auto;
        margin-top: 4px;
    }
    .promoted-badge {
        flex: 0 0 44px;
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
    @media (max-width: 767px) {
        .promoted-header {
            display: block !important;
            padding: 18px !important;
        }
        .promoted-heading {
            align-items: flex-start;
        }
        .promoted-category-subtitle {
            font-size: 13px !important;
            line-height: 1.45;
        }
        .promoted-view-all {
            margin-top: 14px;
        }
    }
    </style>
    @endif
@endif

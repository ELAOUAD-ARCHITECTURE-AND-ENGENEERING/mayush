@if(get_setting('promoted_category_status') == '1' && get_setting('promoted_category_id'))
    @php
        $promoted_category = \App\Models\Category::find(get_setting('promoted_category_id'));
        $promoted_products = collect();
        if($promoted_category) {
            if (!function_exists('getAllChildrenIdsBlade')) {
                function getAllChildrenIdsBlade($category, &$ids = []) {
                    foreach ($category->categories as $child) {
                        $ids[] = $child->id;
                        getAllChildrenIdsBlade($child, $ids);
                    }
                    return $ids;
                }
            }
            $category_ids = getAllChildrenIdsBlade($promoted_category);
            $category_ids[] = $promoted_category->id;

            $promoted_products = \App\Models\Product::whereIn('category_id', $category_ids)
                ->where('published', 1)
                ->where('approved', 1)
                ->where('discount', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
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
                            <i class="las la-fire"></i>
                        </div>
                        <div>
                            <h4 class="fs-18 fw-700 mb-0 text-dark">{{ $promoted_category->getTranslation('name') }}</h4>
                            <span class="fs-12 text-muted">{{ translate('Special promotions on selected products') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('products.category', $promoted_category->slug) }}" class="btn btn-sm btn-outline-primary hov-svg-white rounded-pill px-3">
                        {{ translate('View All') }} <i class="las la-arrow-right ml-1"></i>
                    </a>
                </div>

                {{-- Product Grid --}}
                <div class="px-3 pb-3 pt-2">
                    <div class="aiz-carousel" data-items="6" data-xl-items="5" data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-dots='false' data-infinite='true' data-autoplay='true'>
                        @foreach($promoted_products as $key => $product)
                            <div class="carousel-box px-1">
                                @include('frontend.partials.product_box_1', ['product' => $product])
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
    }
    .promoted-header {
        background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);
        border-bottom: 1px solid #fde68a;
    }
    .promoted-badge {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #f97316, #ef4444);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 22px;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }
    .promoted-category-section .aiz-carousel .slick-prev,
    .promoted-category-section .aiz-carousel .slick-next {
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        width: 36px;
        height: 36px;
    }
    </style>
    @endif
@endif

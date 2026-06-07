@if(get_setting('promoted_category_status') == '1' && get_setting('promoted_category_id'))
    @php
        $lang = get_system_language()->code;
        $promoted_category_id = get_setting('promoted_category_id');
        $promoted_category = $promoted_category_id ? \App\Models\Category::find($promoted_category_id) : null;
        $promoted_category_name = $promoted_category ? $promoted_category->getTranslation('name', $lang) : '';
        $promoted_products = collect();
        $promoted_category_subtitle = trim((string) get_setting('promoted_category_subtitle', '', $lang));
        $promoted_category_subtitle = $promoted_category_subtitle !== ''
            ? $promoted_category_subtitle
            : translate('Des espaces inspirants pour plus d’efficacité Découvrez notre sélection exclusive de mobilier de bureau alliant design, confort et fonctionnalité.');
        
        if($promoted_category) {
            // Safer way to get child IDs - using the 'categories' relationship defined in the model
            $category_ids = array_merge([$promoted_category->id], $promoted_category->categories->pluck('id')->toArray());
            
            $promoted_products = app(\App\Services\StorefrontDataService::class)->promotedCategoryProducts($category_ids);
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
                            <h2 class="promoted-category-title fs-18 fw-700 mb-1 text-dark">{{ $promoted_category_name }}</h2>
                            @if ($promoted_category_subtitle)
                                <h3 class="promoted-category-subtitle fs-14 fw-500 text-muted mb-0">{{ $promoted_category_subtitle }}</h3>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('products.category', $promoted_category->slug) }}" class="promoted-view-all btn btn-sm btn-outline-primary hov-svg-white rounded-pill px-4">
                        {{ translate('View All') }} <i class="las la-arrow-right ml-1"></i>
                    </a>
                </div>

                {{-- Split Content Grid (50/50 Westwing Structure with Liquid Glass Style) --}}
                <div class="p-4 bg-white border-top">
                    <div class="row align-items-stretch">
                        
                        {{-- Left Column: Large Category Banner (50%) --}}
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <a href="{{ route('products.category', $promoted_category->slug) }}" class="d-block promoted-banner-wrap">
                                <img src="{{ optimized_static_asset('assets/img/office_furniture_4k.png', 'medium') }}"
                                     alt="{{ $promoted_category_name }}" 
                                     width="1024" height="1024"
                                     loading="lazy" decoding="async"
                                     class="promoted-banner-img skeleton-shimmer"
                                     onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </a>
                        </div>

                        {{-- Right Column: 2x2 Product Grid (50%) --}}
                        <div class="col-lg-6">
                            <div class="row h-100 gutters-15">
                                @foreach($promoted_products->take(4) as $key => $product)
                                    <div class="col-6 mb-4">
                                        <div class="h-100">
                                            @include('frontend.metro.partials.product_box_2', ['product' => $product])
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
    .promoted-category-card {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(226, 224, 214, 0.6) !important; /* Off-White Border */
        box-shadow: 0 8px 32px 0 rgba(18, 25, 42, 0.05) !important; /* Midnight Navy soft shadow */
        border-radius: 20px !important;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        overflow: hidden;
    }
    .promoted-category-card:hover {
        box-shadow: 0 20px 40px 0 rgba(18, 25, 42, 0.1) !important;
        transform: translateY(-2px) !important;
        border-color: rgba(214, 162, 78, 0.25) !important; /* Warm Gold hover border */
    }
    .promoted-header {
        background: linear-gradient(135deg, rgba(214, 162, 78, 0.03) 0%, rgba(201, 132, 70, 0.05) 100%) !important; /* Warm Gold to Soft Orange */
        border-bottom: 1px solid rgba(226, 224, 214, 0.5) !important;
        gap: 18px;
        padding: 1.5rem 2rem !important;
    }
    .promoted-category-title {
        font-family: var(--mayush-font-heading) !important;
        font-size: 20px !important;
        font-weight: 700 !important;
        letter-spacing: 1px !important;
        color: #12192A !important; /* Midnight Navy */
    }
    .promoted-category-subtitle {
        font-family: var(--mayush-font-body) !important;
        font-size: 14px !important;
        color: #21293E !important; /* Dark Slate Blue */
        line-height: 1.5 !important;
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
        font-family: var(--mayush-font-body) !important;
        font-weight: 600 !important;
        color: #D6A24E !important; /* Warm Gold outline */
        border-color: #D6A24E !important;
        border-radius: 30px !important;
        padding: 8px 24px !important;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        flex: 0 0 auto;
        margin-top: 4px;
    }
    .promoted-view-all:hover {
        background: #D6A24E !important;
        color: #ffffff !important;
        border-color: #D6A24E !important;
        box-shadow: 0 6px 15px rgba(214, 162, 78, 0.3) !important;
    }
    .promoted-badge {
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #D6A24E, #C98446) !important; /* Warm Gold to Soft Orange */
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: 0 4px 12px rgba(214, 162, 78, 0.3) !important;
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
            display: inline-block;
        }
    }

    /* Split Content Banner Styles */
    .promoted-banner-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        border: 1px solid rgba(226, 224, 214, 0.6);
        box-shadow: 0 4px 20px rgba(18, 25, 42, 0.02);
        height: 100%;
        min-height: 480px;
        display: block;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .promoted-banner-wrap:hover {
        box-shadow: 0 12px 30px rgba(18, 25, 42, 0.08);
        border-color: rgba(214, 162, 78, 0.25);
        transform: translateY(-2px);
    }
    .promoted-banner-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .promoted-banner-wrap:hover .promoted-banner-img {
        transform: scale(1.04) !important;
    }
    @media (max-width: 991px) {
        .promoted-banner-wrap {
            min-height: 300px;
        }
    }
    </style>
    @endif
@endif

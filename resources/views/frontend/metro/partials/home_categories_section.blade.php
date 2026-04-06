@if (get_setting('home_categories') != null)
    @php
        $home_categories = json_decode(get_setting('home_categories'));
        $categories = get_category($home_categories);
    @endphp
    @if (count($categories) > 0)
        <style>
            .metro-category-row {
                background: #fff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                margin-bottom: 2rem;
                display: flex;
                flex-wrap: wrap;
                border: 1px solid #f1f5f9;
            }
            .metro-category-sidebar {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 2.5rem 1.5rem;
                min-height: 300px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                border-right: 1px solid #dee2e6;
                transition: all 0.3s ease;
                width: 100%;
            }
            @media (min-width: 992px) {
                .metro-category-sidebar { width: 220px; }
            }
            @media (min-width: 1400px) {
                .metro-category-sidebar { width: 260px; }
            }
            .metro-category-sidebar:hover {
                background: #ffffff;
                box-shadow: 10px 0 20px rgba(0,0,0,0.05);
            }
            .metro-category-image {
                width: 80px;
                height: 80px;
                object-fit: contain;
                margin-bottom: 1.5rem;
                border-radius: 12px;
                background: white;
                padding: 10px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            }
            .metro-category-btn-view-all {
                background: #67308f;
                color: #fff;
                border-radius: 50px;
                padding: 0.6rem 1.8rem;
                font-size: 0.85rem;
                font-weight: 700;
                margin-top: 1.5rem;
                text-transform: uppercase;
                transition: all 0.3s ease;
                border: none;
            }
            .metro-category-btn-view-all:hover {
                background: #542775;
                color: #fff;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(103, 48, 143, 0.4);
            }
            .metro-category-products-container {
                flex: 1;
                padding: 1rem;
                min-width: 0;
            }
        </style>
        
        <div class="py-4">
            @foreach ($categories as $category_key => $category)
                @php
                    $category_name = $category->getTranslation('name');
                    $product_ids = get_cached_products($category->id);
                @endphp
                @if (count($product_ids) > 0)
                    <section class="mb-5">
                        <div class="container">
                            <div class="metro-category-row hover-shadow-lg has-transition">
                                <!-- Category Details Box -->
                                <div class="metro-category-sidebar position-relative z-1">
                                    <img src="{{ uploaded_asset($category->banner, 'medium') }}" 
                                        class="metro-category-image" 
                                        onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                    <div class="z-1 w-100">
                                        <h3 class="fs-18 fw-700 text-dark mb-1">{{ $category_name }}</h3>
                                        <div class="rating rating-sm justify-content-center mb-3">
                                            <i class="las la-star active"></i><i class="las la-star active"></i><i class="las la-star active"></i><i class="las la-star active"></i><i class="las la-star active"></i>
                                        </div>
                                        <a href="{{ route('products.category', $category->slug) }}" class="btn metro-category-btn-view-all">
                                            {{ translate('Browse') }}
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Products Carousel -->
                                <div class="metro-category-products-container">
                                    <div class="aiz-carousel gutters-10 arrow-dark arrow-inactive-none" 
                                        data-items="5" data-xxl-items="5" data-xl-items="4" data-lg-items="3" 
                                        data-md-items="3" data-sm-items="2" data-xs-items="2" 
                                        data-arrows='true' data-autoplay='true' data-infinite='true'>
                                        @foreach ($product_ids as $product_key => $product)
                                            <div class="carousel-box border rounded bg-white overflow-hidden text-center has-transition mx-1">
                                                @include('frontend.metro.partials.product_box_2', ['product' => $product])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
    @endif
@endif

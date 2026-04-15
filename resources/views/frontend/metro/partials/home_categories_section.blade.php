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
                position: relative;
                overflow: hidden;
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
                color: #fff;
            }
            @media (min-width: 992px) {
                .metro-category-sidebar { width: 220px; }
            }
            @media (min-width: 1400px) {
                .metro-category-sidebar { width: 260px; }
            }
            .metro-category-sidebar:hover .metro-category-bg {
                transform: scale(1.1);
            }
            .metro-category-bg {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 0;
                object-fit: cover;
                transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
            }
            .metro-category-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);
                z-index: 1;
            }
            .metro-category-sidebar-content {
                position: relative;
                z-index: 2;
                width: 100%;
            }
            .metro-category-image {
                width: 70px;
                height: 70px;
                object-fit: contain;
                margin-bottom: 1.5rem;
                border-radius: 50%;
                background: rgba(255,255,255,0.9);
                padding: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                border: 2px solid rgba(255,255,255,0.5);
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
                border: 2px solid rgba(255,255,255,0.2);
            }
            .metro-category-btn-view-all:hover {
                background: #fff;
                color: #67308f;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
                border-color: #67308f;
            }
            .metro-category-products-container {
                flex: 1;
                padding: 1rem;
                min-width: 0;
            }
            .metro-category-sidebar h3 {
                color: #fff !important;
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
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
                                <div class="metro-category-sidebar">
                                    <!-- Background Image -->
                                    <img src="{{ uploaded_asset($category->banner) }}" 
                                        class="metro-category-bg" 
                                        onerror="this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}'">
                                    
                                    <!-- Overlay mask -->
                                    <div class="metro-category-overlay"></div>

                                    <!-- Content -->
                                    <div class="metro-category-sidebar-content">
                                        <img src="{{ uploaded_asset($category->icon, 'small') }}" 
                                            class="metro-category-image" 
                                            onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                        
                                        <h3 class="fs-18 fw-700 mb-1">{{ $category_name }}</h3>
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

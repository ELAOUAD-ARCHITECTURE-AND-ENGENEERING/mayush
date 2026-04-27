@extends('frontend.layouts.app')

@php
    $homepageSeoTitle = translate('Mayush Marketplace for Furniture, Decor and Interior Design in Morocco');
    $homepageSeoDescription = translate('Discover furniture, decor, lighting, home materials and interior design products from Mayush sellers in Morocco.');
    $homepageSeoImage = uploaded_asset(get_setting('meta_image') ?: get_setting('header_logo'));
    $homepageStats = app(\App\Services\SeoStatsService::class)->homepageStats();
    $featured_categories = $featured_categories ?? collect();
    $hot_categories = $hot_categories ?? collect();
    $todays_deal_products = $todays_deal_products ?? collect();
    $newest_products = $newest_products ?? collect();
@endphp

@section('meta_title'){{ $homepageSeoTitle }}@stop
@section('meta_description'){{ $homepageSeoDescription }}@stop
@section('meta_image'){{ $homepageSeoImage }}@stop
@section('canonical_url'){{ route('home') }}@stop

@section('meta')
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::webPageSchema([
        'title' => $homepageSeoTitle,
        'description' => $homepageSeoDescription,
        'canonical' => route('home'),
    ])) !!}</script>
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(app(\App\Services\SeoStatsService::class)->homepageFaqSchema()) !!}</script>
@endsection

@section('content')
    <h1 class="d-none">Mayush Marketplace : meubles, decoration et design interieur au Maroc</h1>
    {{-- Categories , Sliders . Today's deal --}}
    <div class="home-banner-area mb-4 pt-3">
        <div class="container">
            <div class="row gutters-10 position-relative d-flex flex-wrap">
                <div class="col-lg-3 position-static d-none d-lg-block">
                    @include('frontend.partials.category_menu')
                </div>

                @php
                    $num_todays_deal = count($todays_deal_products);
                @endphp

                <div class="@if($num_todays_deal > 0) col-lg-7 @else col-lg-9 @endif">
                    @if (get_setting('home_slider_images') != null)
                        <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-items="1" data-xl-items="1" data-lg-items="1" data-md-items="1" data-sm-items="1" data-arrows="true" data-dots="true" data-autoplay="true">
                            @php $slider_images = json_decode(get_setting('home_slider_images'), true);  @endphp
                            @foreach ($slider_images as $key => $value)
                                <div class="carousel-box">
                                    <a href="{{ json_decode(get_setting('home_slider_links'), true)[$key] }}">
                                        <img
                                            class="d-block mw-100 img-fit rounded shadow-sm overflow-hidden"
                                            src="{{ uploaded_asset($slider_images[$key]) }}"
                                            alt="{{ env('APP_NAME')}} promo"
                                            @if(count($featured_categories) == 0)
                                            height="457"
                                            @else
                                            height="315"
                                            @endif
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if (count($featured_categories) > 0)
                        <ul class="d-flex flex-wrap list-unstyled mb-0 row gutters-5">
                            @foreach ($featured_categories as $key => $category)
                                <li class="minw-0 col-4 col-md mt-3">
                                    <a href="{{ route('products.category', $category->slug) }}" class="d-block rounded bg-white p-2 text-reset shadow-sm">
                                        <img
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($category->banner) }}"
                                            alt="{{ $category->getTranslation('name') }} furniture and decor on Mayush"
                                            class="lazyload img-fit"
                                            height="78"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                                        <div class="text-truncate fs-12 fw-600 mt-2 opacity-70">{{ $category->getTranslation('name') }}</div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if($num_todays_deal > 0)
                <div class="col-lg-2 order-3 mt-3 mt-lg-0">
                    <div class="bg-white rounded shadow-sm">
                        <div class="bg-soft-primary rounded-top p-3 d-flex align-items-center justify-content-center">
                            <span class="fw-600 fs-16 mr-2 text-truncate">
                                {{ translate('Todays Deal') }}
                            </span>
                            <span class="badge badge-primary badge-inline">{{ translate('Hot') }}</span>
                        </div>
                        <div class="c-scrollbar-light overflow-auto h-lg-400px p-2 bg-primary rounded-bottom">
                            <div class="gutters-5 lg-no-gutters row d-flex flex-wrap row-cols-2 row-cols-lg-1 d-flex flex-wrap">
                            @foreach ($todays_deal_products as $key => $product)
                                @if ($product != null)
                                <div class="col mb-2">
                                    <a href="{{ route('product', $product->slug) }}" class="d-block p-2 text-reset bg-white h-100 rounded">
                                        <div class="row d-flex flex-wrap gutters-5 align-items-center">
                                            <div class="col-xxl">
                                                <div class="img">
                                                    <img
                                                        class="lazyload img-fit h-140px h-lg-80px"
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                        alt="{{ $product->getTranslation('name') }} - Mayush"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-xxl text-center">
                                                <div class="fs-16">
                                                    <span class="d-block text-primary fw-600">{{ home_discounted_base_price($product) }}</span>
                                                    @if(home_base_price($product) != home_discounted_base_price($product))
                                                        <del class="d-block opacity-70">{{ home_base_price($product) }}</del>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endif
                            @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>


    {{-- Banner section 1 --}}
    @if (get_setting('home_banner1_images') != null)
    <div class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_1_imags = json_decode(get_setting('home_banner1_images')); @endphp
                @foreach ($banner_1_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner1_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_1_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif


    {{-- Flash Deal --}}
    @php
        $flash_deal = \App\Models\FlashDeal::where('status', 1)->where('featured', 1)->first();
        $is_valid_deal = false;
        if($flash_deal != null && $flash_deal->is_active){
            $flash_deal_products = $flash_deal->flash_deal_products()->with('product')->get();
            if(count($flash_deal_products) > 0){
                $is_valid_deal = true;
            }
        }
    @endphp
    @if($is_valid_deal)
    <section class="mb-4">
        <div class="container">
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">

                <div class="d-flex flex-wrap mb-3 align-items-baseline border-bottom">
                    <h3 class="h5 fw-700 mb-0">
                        <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Flash Sale') }}</span>
                    </h3>
                    <div class="aiz-count-down ml-auto ml-lg-3 align-items-center" data-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}"></div>
                    <a href="{{ route('flash-deal-details', $flash_deal->slug) }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md w-100 w-md-auto">{{ translate('View More') }}</a>
                </div>

                <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                    @foreach ($flash_deal->flash_deal_products->take(20) as $key => $flash_deal_product)
                        @php
                            $product = \App\Models\Product::find($flash_deal_product->product_id);
                        @endphp
                        @if ($product != null && $product->published != 0)
                            <div class="carousel-box">
                                @include('frontend.partials.product_box_1',['product' => $product])
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif


    <div id="section_newest">
        @if (count($newest_products) > 0)
            <section class="mb-4">
                <div class="container">
                    <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">
                                    {{ translate('New Products') }}
                                </span>
                            </h3>
                        </div>
                        <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                            @foreach ($newest_products as $key => $new_product)
                            <div class="carousel-box">
                                @include('frontend.partials.product_box_1',['product' => $new_product])
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>   
        @endif
    </div>

    @include('frontend.partials.promoted_category_section')

    {{-- Featured Section --}}



    <div id="section_featured">
        <section class="mb-4">
            <div class="container">
                <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Best Selling  --}}
    <div id="section_best_selling">
        <section class="mb-4">
            <div class="container">
                <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Auction Product -->
    @if(addon_is_activated('auction'))
        <div id="auction_products">
            <section class="mb-4">
                <div class="container">
                    <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                        <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                            @for ($i=0; $i<5; $i++)
                                <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                            @endfor
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endif



    {{-- Banner Section 2 --}}
    @if (get_setting('home_banner2_images') != null)
    <div class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_2_imags = json_decode(get_setting('home_banner2_images')); @endphp
                @foreach ($banner_2_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner2_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_2_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Category wise Products --}}
    <div id="section_home_categories">
        <section class="mb-4">
            <div class="container">
                <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Classified Product Redesign (Phase 4) --}}
    @if(get_setting('classified_product') == 1)
        @php
            $classified_products = \App\Models\CustomerProduct::where('customer_products.status', '1')->where('customer_products.published', '1')
                ->leftJoin('promotions', function($join) {
                    $join->on('customer_products.id', '=', 'promotions.product_id')
                         ->where('promotions.status', 'approved')
                         ->where('promotions.start_date', '<=', \Carbon\Carbon::now())
                         ->where('promotions.end_date', '>=', \Carbon\Carbon::now());
                })
                ->select('customer_products.*')
                ->orderByRaw("CASE 
                    WHEN promotions.tier = 'gold' THEN 1 
                    WHEN promotions.tier = 'premium' THEN 2 
                    WHEN promotions.tier = 'standard' THEN 3 
                    ELSE 4 END")
                ->orderBy('customer_products.created_at', 'desc')
                ->take(12)
                ->get();
        @endphp
        
        @if (count($classified_products) > 0)
            <section class="mb-4">
                <div class="container">
                    <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Classified Ads') }}</span>
                            </h3>
                            <a href="{{ route('customer.products') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View More') }}</a>
                        </div>
                        
                        <div class="promoted-grid-wrap mobile-grid">
                            {{-- Desktop Slider --}}
                            <div class="aiz-carousel gutters-10 half-outside-arrow" 
                                 data-items="6" data-xl-items="5" data-lg-items="4" 
                                 data-md-items="3" data-sm-items="2" data-xs-items="2" 
                                 data-arrows='true' data-infinite='true'>
                                @foreach ($classified_products as $key => $product)
                                    <div class="carousel-box">
                                        @include('frontend.partials.promoted_product_box', ['product' => $product])
                                    </div>
                                @endforeach
                            </div>

                            {{-- Mobile Grid (2-row masonry-like) --}}
                            <div class="promoted-grid-mobile">
                                @foreach ($classified_products->take(6) as $product)
                                    @include('frontend.partials.promoted_product_box', ['product' => $product])
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    @endif

    {{-- Banner Section 2 --}}
    @if (get_setting('home_banner3_images') != null)
    <div class="mb-4">
        <div class="container">
            <div class="row gutters-10">
                @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
                @foreach ($banner_3_imags as $key => $value)
                    <div class="col-xl col-md-6">
                        <div class="mb-3 mb-lg-0">
                            <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}" class="d-block text-reset">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_3_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Best Seller --}}
    <div id="section_best_sellers">
        <section class="mb-4">
            <div class="container">
                <div class="skeleton-shimmer h-150px w-100 rounded"></div>
            </div>
        </section>
    </div>

    {{-- Elite Artisans --}}
    <div id="load-elite-artisans-section">
        <section class="mb-4">
            <div class="container text-center">
                <div class="skeleton-shimmer h-150px w-100 rounded"></div>
            </div>
        </section>
    </div>

    {{-- Top 10 categories --}}
    @if (get_setting('top10_categories') != null)
    @php
        $col_section = 'col-lg-12';
        $col_block = 'col-xl-3 col-lg-4 col-sm-6';
    @endphp
    <section class="mb-4">
        <div class="container">
            <div class="row gutters-10 d-flex flex-wrap">
                @if (get_setting('top10_categories') != null)
                    <div class="{{ $col_section }}">
                        <div class="d-flex mb-3 align-items-baseline border-bottom">
                            <h3 class="h5 fw-700 mb-0">
                                <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Top 10 Categories') }}</span>
                            </h3>
                            <a href="{{ route('categories.all') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View All Categories') }}</a>
                        </div>
                        <div class="row gutters-5 d-flex flex-wrap">
                            @php $top10_categories = json_decode(get_setting('top10_categories')); @endphp
                            @foreach ($top10_categories as $key => $value)
                                @php $category = \App\Models\Category::find($value); @endphp
                                @if ($category != null)
                                    <div class="{{ $col_block }}">
                                        <a href="{{ route('products.category', $category->slug) }}" class="bg-white border d-block text-reset rounded p-2 hov-shadow-md mb-2">
                                            <div class="row align-items-center no-gutters">
                                                <div class="col-3 text-center">
                                                    <img
                                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                        data-src="{{ uploaded_asset($category->banner) }}"
                                                        alt="{{ $category->getTranslation('name') }} furniture and decor on Mayush"
                                                        class="img-fluid img lazyload h-60px"
                                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                                    >
                                                </div>
                                                <div class="col-7">
                                                    <div class="text-truncat-2 pl-3 fs-14 fw-600 text-left">{{ $category->getTranslation('name') }}</div>
                                                </div>
                                                <div class="col-2 text-center">
                                                    <i class="la la-angle-right text-primary"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- SEO Authority Section --}}
    <section class="mb-5 py-5 bg-light border-top border-bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <h2 class="h3 fw-700 text-dark mb-4">Mayush : marketplace furniture and interior design in Morocco</h2>
                    <div class="fs-16 text-gray fw-400 lh-1-8">
                        <p class="mb-4">
                            <strong>Mayush connects customers with furniture, decor, lighting and home-material sellers in Morocco.</strong>
                            The marketplace helps buyers compare products, discover seller shops and plan interiors with practical product information.
                        </p>
                        <div class="row gutters-15 mt-5">
                            <div class="col-md-4 mb-4 mb-md-0">
                                <div class="p-3 bg-white rounded shadow-sm h-100">
                                    <h4 class="fs-18 fw-700 text-primary">Product selection</h4>
                                    <p class="fs-14 m-0">
                                        @if ($homepageStats['published_products'] !== null)
                                            Explore {{ number_format($homepageStats['published_products']) }} approved published products on Mayush.
                                        @else
                                            Explore approved furniture, decor and interior design products on Mayush.
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4 mb-md-0">
                                <div class="p-3 bg-white rounded shadow-sm h-100">
                                    <h4 class="fs-18 fw-700 text-primary">Verified sellers</h4>
                                    <p class="fs-14 m-0">
                                        @if ($homepageStats['verified_sellers'] !== null)
                                            Shop from {{ number_format($homepageStats['verified_sellers']) }} verified sellers across the marketplace.
                                        @else
                                            Shop from sellers listed on the Mayush marketplace.
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm h-100">
                                    <h4 class="fs-18 fw-700 text-primary">Delivery confidence</h4>
                                    <p class="fs-14 m-0">
                                        @if ($homepageStats['delivery_success_rate'] !== null)
                                            Recent delivered-order rate: {{ $homepageStats['delivery_success_rate'] }}% over the last 180 days.
                                        @else
                                            Delivery information is handled by the seller and order workflow for each purchase.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $.post('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_featured').html(data);
                AIZ.plugins.slickCarousel();
            });
            $.post('{{ route('home.section.best_selling') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_best_selling').html(data);
                AIZ.plugins.slickCarousel();
            });
            $.post('{{ route('home.section.auction_products') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#auction_products').html(data);
                AIZ.plugins.slickCarousel();
            });
            $.post('{{ route('home.section.home_categories') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_home_categories').html(data);
                AIZ.plugins.slickCarousel();
            });
            $.post('{{ route('home.section.best_sellers') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_best_sellers').html(data);
                AIZ.plugins.slickCarousel();
            });
            $.post('{{ route('load-elite-artisans-section') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#load-elite-artisans-section').html(data);
                AIZ.plugins.slickCarousel();
            });
        });
    </script>
@endsection

@extends('frontend.layouts.app')

@php
    $homepageSeoTitle = translate('Mayush Marketplace for Furniture, Decor and Interior Design in Morocco');
    $homepageSeoDescription = translate('Discover furniture, decor, lighting, home materials and interior design products from Mayush sellers in Morocco.');
    $homepageSeoImage = uploaded_asset(get_setting('meta_image') ?: get_setting('header_logo'));
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
    <style>
        #section_featured .slick-slider .slick-list{
            background: #fff;
        }
        #section_featured .slick-slider .slick-list .slick-slide {
            margin-bottom: -5px;
        }
        @media (max-width: 575px){
            #section_featured .slick-slider .slick-list .slick-slide {
                margin-bottom: -4px;
            }
        }
    </style>

    @php $lang = get_system_language()->code;  @endphp
    
    <!-- 1. Home Slider -->
    <div class="home-banner-area mb-3">
        <div class="p-0">
            <div class="home-slider slider-full">
                @if (get_setting('home_slider_images', null, $lang) != null)
                    <div class="aiz-carousel dots-inside-bottom mobile-img-auto-height" data-autoplay="true" data-infinite="true">
                        @php
                            $decoded_slider_images = json_decode(get_setting('home_slider_images', null, $lang), true);
                            $sliders = get_slider_images($decoded_slider_images);
                            $home_slider_links = get_setting('home_slider_links', null, $lang);
                        @endphp
                        @foreach ($sliders as $key => $slider)
                            <div class="carousel-box h-auto">
                                <a href="{{ isset(json_decode($home_slider_links, true)[$key]) ? json_decode($home_slider_links, true)[$key] : '' }}">
                                    <div class="d-block mw-100 img-fit overflow-hidden h-180px h-md-320px h-lg-460px h-xl-553px">
                                        <img class="img-fit h-100 m-auto has-transition"
                                        src="{{ $slider ? my_asset($slider->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                        alt="Mayush furniture and decor marketplace promotion"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 14. Promotional Category (Moved Below Hero - Direct Include for local stability) -->
    @include('frontend.partials.promoted_category_section')

    <!-- 2. Flash Deals Section (Cyber Monday Style) -->
    @include('frontend.metro.partials.flash_deals_section')

    <!-- 3. Flash Deals Navigation (All available deals) -->
    @php
        $active_flash_deals = get_active_flash_deals();
    @endphp
    @if (get_setting('flash_deals_navigation_activation') == 1 && count($active_flash_deals) > 0)
    <section class="mb-4">
        <div class="container">
            <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="7" data-xl-items="6" data-lg-items="5" data-md-items="4" data-sm-items="3" data-xs-items="2" data-arrows='true'>
                        @foreach ($active_flash_deals as $key => $flash_deal_item)
                            <div class="carousel-box">
                                <a href="{{ route('flash-deal-details', $flash_deal_item->slug) }}" class="d-block text-reset text-center">
                                    <div class="flash-nav-item img-fit h-100px h-md-140px mb-1" style="background: #fdf2f2; border: 1px solid #fee2e2; border-radius: 12px; overflow: hidden;">
                                        <img draggable="false" class="lazyload img-fit p-3"
                                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                            data-src="{{ uploaded_asset($flash_deal_item->banner) }}"
                                            alt="{{ $flash_deal_item->getTranslation('title') }} - Mayush"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </div>
                                    <div class="flash-nav-text text-truncate fs-13 fw-600 mt-2">
                                        {{ $flash_deal_item->getTranslation('title') }}
                                    </div>
                                </a>
                            </div>
                        @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- 4. Today's Deal (Yellow Banner Style) -->
    <div id="todays_deal_section">
        <section class="mb-4">
            <div class="container">
                <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- 5. Category Icon Navigation -->
    @include('frontend.metro.partials.category_icon_navigation')

    <!-- 6. Featured Products -->
    <div id="section_featured">
        <section class="mb-4">
            <div class="container">
                <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- 7. Banner Level 1 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner1'])

    <!-- 8. Featured Categories -->
    @include('frontend.metro.partials.featured_categories_section')

    <!-- 9. Banner Level 2 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner2'])

    <!-- 10. Best Selling -->
    <div id="section_best_selling">
        <section class="mb-4">
            <div class="container">
                <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- 11. New Products -->
    <div id="section_newest">
        <section class="mb-4">
            <div class="container">
                <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- 12. Banner Level 3 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner3'])

    <!-- 13. Category Wise Products -->
    <div id="section_home_categories">
        <section class="mb-4">
            <div class="container">
                <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- 15. Marketplace Banner -->
    @include('frontend.metro.partials.marketplace_banner')

    <!-- 16. Top Sellers (Hidden by default, loaded via AJAX only if criteria met) -->
    <div id="section_best_sellers"></div>

    <!-- 17. Top Brands (Hidden as requested) -->
    {{-- @include('frontend.metro.partials.top_brands_section') --}}

    <!-- 18. Classifieds -->
    @include('frontend.metro.partials.classifieds_section')

@endsection

@section('script')
    <script>
        $(document).ready(function(){
            // Load sections via AJAX for maximum performance
            loadSection('{{ route('home.section.featured') }}', '#section_featured');
            loadSection('{{ route('home.section.best_selling') }}', '#section_best_selling');
            loadSection('{{ route('home.section.newest_products') }}', '#section_newest');
            loadSection('{{ route('home.section.home_categories') }}', '#section_home_categories');
            loadSection('{{ route('home.section.todays_deal') }}', '#todays_deal_section');
            
            // Re-load best sellers with forced logic
            loadSection('{{ route('home.section.best_sellers') }}', '#section_best_sellers');

            function loadSection(url, target) {
                $.get(url, function(data){
                    $(target).html(data);
                    // Critical: Re-initialize slick carousel for the new content
                    setTimeout(function(){
                        AIZ.plugins.slickCarousel();
                        AIZ.extra.plusMinus();
                    }, 100);
                });
            }
        });
    </script>
@endsection

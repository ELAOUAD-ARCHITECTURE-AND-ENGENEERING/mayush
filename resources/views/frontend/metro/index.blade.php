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
        .metro-hero-slide {
            position: relative;
            overflow: hidden;
            background: #111;
        }
        .metro-hero-slide.has-content::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(90deg, rgba(0, 0, 0, .66) 0%, rgba(0, 0, 0, .42) 46%, rgba(0, 0, 0, .08) 100%);
            pointer-events: none;
        }
        .metro-hero-content {
            position: absolute;
            z-index: 2;
            top: 50%;
            left: 7%;
            width: min(620px, 86%);
            transform: translateY(-50%);
            color: #fff;
            text-shadow: 0 2px 16px rgba(0, 0, 0, .35);
        }
        .metro-hero-title {
            font-size: 44px;
            line-height: 1.08;
            font-weight: 800;
            letter-spacing: 0;
            margin-bottom: 14px;
        }
        .metro-hero-title span,
        .metro-hero-title strong,
        .metro-hero-title b,
        .metro-hero-title em,
        .metro-hero-title i,
        .metro-hero-title u {
            line-height: inherit;
        }
        .metro-hero-description {
            max-width: 560px;
            font-size: 17px;
            line-height: 1.65;
            margin-bottom: 22px;
            color: rgba(255, 255, 255, .92);
        }
        .metro-hero-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 700;
            text-shadow: none;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .22);
        }
        @media (min-width: 1200px) {
            .metro-hero-title {
                font-size: 56px;
            }
        }
        @media (max-width: 767px) {
            .home-slider .metro-hero-slide {
                height: 300px !important;
                min-height: 300px;
            }
            .metro-hero-slide.has-content::after {
                background: linear-gradient(0deg, rgba(0, 0, 0, .72) 0%, rgba(0, 0, 0, .42) 62%, rgba(0, 0, 0, .08) 100%);
            }
            .metro-hero-content {
                top: auto;
                bottom: 20px;
                left: 18px;
                right: 18px;
                width: auto;
                transform: none;
            }
            .metro-hero-title {
                max-width: 92%;
                font-size: 22px;
                line-height: 1.18;
                margin-bottom: 8px;
            }
            .metro-hero-description {
                max-width: 94%;
                font-size: 13px;
                line-height: 1.42;
                margin-bottom: 12px;
            }
            .metro-hero-cta {
                max-width: 100%;
                min-height: 38px;
                padding: 8px 14px;
                font-size: 12px;
                white-space: normal;
                text-align: center;
            }
        }
        @media (max-width: 420px) {
            .home-slider .metro-hero-slide {
                height: 280px !important;
                min-height: 280px;
            }
            .metro-hero-content {
                bottom: 16px;
                left: 16px;
                right: 16px;
            }
            .metro-hero-title {
                font-size: 20px;
                line-height: 1.16;
            }
            .metro-hero-description {
                font-size: 12px;
                line-height: 1.38;
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
                            $home_slider_links = json_decode(get_setting('home_slider_links', null, $lang), true) ?: [];
                            $home_slider_titles = json_decode(get_setting('home_slider_titles', null, $lang), true) ?: [];
                            $home_slider_descriptions = json_decode(get_setting('home_slider_descriptions', null, $lang), true) ?: [];
                            $home_slider_cta_texts = json_decode(get_setting('home_slider_cta_texts', null, $lang), true) ?: [];
                            $home_slider_cta_links = json_decode(get_setting('home_slider_cta_links', null, $lang), true) ?: [];
                        @endphp
                        @foreach ($sliders as $key => $slider)
                            @php
                                $slideLink = trim((string) ($home_slider_links[$key] ?? ''));
                                $slideTitle = trim((string) ($home_slider_titles[$key] ?? ''));
                                $slideDescription = trim((string) ($home_slider_descriptions[$key] ?? ''));
                                $configuredCtaText = trim((string) ($home_slider_cta_texts[$key] ?? ''));
                                $configuredCtaLink = trim((string) ($home_slider_cta_links[$key] ?? ''));
                                $hasHeroContent = $slideTitle !== '' || $slideDescription !== '' || $configuredCtaText !== '' || $configuredCtaLink !== '';
                                $slideCtaText = $configuredCtaText !== '' ? $configuredCtaText : ($configuredCtaLink !== '' ? translate('Shop Now') : '');
                                $slideCtaLink = $configuredCtaLink !== '' ? $configuredCtaLink : ($slideLink !== '' ? $slideLink : route('search'));
                                $slideTitleText = trim(strip_tags($slideTitle));
                            @endphp
                            <div class="carousel-box h-auto">
                                <div class="metro-hero-slide {{ $hasHeroContent ? 'has-content' : '' }} d-block mw-100 img-fit h-180px h-md-320px h-lg-460px h-xl-553px">
                                    @if (!$hasHeroContent && $slideLink)
                                        <a class="d-block h-100" href="{{ $slideLink }}">
                                            <img class="img-fit h-100 m-auto has-transition"
                                            src="{{ $slider ? my_asset($slider->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                            alt="{{ $slideTitleText ?: translate('Mayush furniture and decor marketplace promotion') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                        </a>
                                    @else
                                        <img class="img-fit h-100 m-auto has-transition"
                                        src="{{ $slider ? my_asset($slider->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                        alt="{{ $slideTitleText ?: translate('Mayush furniture and decor marketplace promotion') }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                    @endif
                                    @if ($hasHeroContent)
                                        <div class="metro-hero-content">
                                            @if ($slideTitle)
                                                <h1 class="metro-hero-title">{!! app(\App\Services\HeroTitleSanitizerService::class)->sanitize($slideTitle) !!}</h1>
                                            @endif
                                            @if ($slideDescription)
                                                <p class="metro-hero-description">{{ $slideDescription }}</p>
                                            @endif
                                            @if ($slideCtaText && $slideCtaLink)
                                                <a href="{{ $slideCtaLink }}" class="btn btn-primary metro-hero-cta">
                                                    {{ $slideCtaText }}
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. Featured Categories -->
    @include('frontend.metro.partials.featured_categories_section')

    <!-- 3. Today's Deal -->
    <div id="todays_deal_section">
        <section class="mb-4">
            <div class="container">
                <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6">
                        @for ($i=0; $i<6; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- 14. Promotional Category (Moved Below Hero - Direct Include for local stability) -->
    @include('frontend.partials.promoted_category_section')

    <!-- 4. Flash Deals Section (Cyber Monday Style) -->
    @include('frontend.metro.partials.flash_deals_section')

    <!-- 5. Flash Deals Navigation (All available deals) -->
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

    <!-- 6. Category Icon Navigation -->
    @if (get_setting('category_icon_navigation_status', '1') == '1')
        @include('frontend.metro.partials.category_icon_navigation')
    @endif

    <!-- 7. Featured Products -->
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

    <!-- 8. Banner Level 1 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner1'])

    <!-- 9. Banner Level 2 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner2'])

    <!-- 10. New Collections & Best Selling -->
    <section id="metro_collections_section" class="mb-4">
        <div class="container">
            <div class="bg-white px-3 py-4 px-md-4 hov-shadow-md has-transition rounded">
                <div id="section_newest" class="metro-collection-subsection">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
                <div id="section_best_selling" class="metro-collection-subsection mt-4 pt-3 border-top">
                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
                        @for ($i=0; $i<5; $i++)
                            <div class="col mb-3">@include('frontend.metro.partials.product_placeholder_box')</div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 12. Banner Level 3 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner3'])

    @if (get_setting('home_categories_section_status', '1') == '1')
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
    @endif

    <!-- 15. Marketplace Banner -->
    @include('frontend.metro.partials.marketplace_banner')

    <!-- 16. Top Sellers (Hidden by default, loaded via AJAX only if criteria met) -->
    <div id="section_best_sellers"></div>

    <!-- 17. Top Brands (Hidden as requested) -->
    {{-- @include('frontend.metro.partials.top_brands_section') --}}

    <!-- 18. Inspiration Articles -->
    @include('frontend.metro.partials.inspiration_articles_section')

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
            @if (get_setting('home_categories_section_status', '1') == '1')
                loadSection('{{ route('home.section.home_categories') }}', '#section_home_categories');
            @endif
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
                        initMetroTodaysDealCountdown();
                    }, 100);
                });
            }

            function initMetroTodaysDealCountdown() {
                $('[data-metro-todays-countdown]').each(function(){
                    var $timer = $(this);

                    if ($timer.data('countdown-ready')) {
                        return;
                    }

                    $timer.data('countdown-ready', true);

                    function nextMidnight() {
                        var now = new Date();
                        return new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0, 0);
                    }

                    function pad(value) {
                        return String(value).padStart(2, '0');
                    }

                    function updateTimer() {
                        var now = new Date();
                        var remaining = Math.max(0, nextMidnight().getTime() - now.getTime());
                        var totalSeconds = Math.floor(remaining / 1000);
                        var days = Math.floor(totalSeconds / 86400);
                        var hours = Math.floor((totalSeconds % 86400) / 3600);
                        var minutes = Math.floor((totalSeconds % 3600) / 60);
                        var seconds = totalSeconds % 60;

                        $timer.find('[data-countdown-part="days"]').text(pad(days));
                        $timer.find('[data-countdown-part="hours"]').text(pad(hours));
                        $timer.find('[data-countdown-part="minutes"]').text(pad(minutes));
                        $timer.find('[data-countdown-part="seconds"]').text(pad(seconds));
                    }

                    updateTimer();
                    setInterval(updateTimer, 1000);
                });
            }
        });
    </script>
@endsection

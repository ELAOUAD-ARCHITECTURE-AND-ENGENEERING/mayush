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
        /* Luxury Font pairing rules */
        h1, h2, h3, h4,
        .promoted-category-title,
        .fs-22, .fs-28 {
            font-family: 'Cinzel', serif !important;
            font-weight: 700 !important;
            letter-spacing: 1.5px !important;
            color: #12192A !important; /* Midnight Navy */
        }

        /* Banner headings must stay white — separate from the dark heading rule above */
        .metro-hero-title,
        .metro-marketplace-split-title {
            font-family: 'Cinzel', serif !important;
            font-weight: 700 !important;
            letter-spacing: 1.5px !important;
            color: #ffffff !important;
        }

        body, p, span, a, input, button, select, textarea,
        .metro-hero-description,
        .metro-hero-cta,
        .promoted-category-subtitle,
        .promoted-view-all,
        .fs-12, .fs-13, .fs-14, .fs-15, .fs-16, .fs-17 {
            font-family: 'Josefin Sans', sans-serif !important;
        }

        /* Color-mapping rules */
        .text-dark {
            color: #12192A !important;
        }
        .text-primary, .hov-text-primary:hover {
            color: #8A5A12 !important; /* Accessible warm accent for text */
        }
        .text-secondary {
            color: #5F6368 !important;
        }
        .btn-outline-primary {
            color: #D6A24E !important;
            border-color: #D6A24E !important;
        }
        .btn-outline-primary:hover {
            background-color: #D6A24E !important;
            border-color: #D6A24E !important;
            color: #ffffff !important;
        }

        /* Liquid Glass Container Overrides */
        .bg-white.px-3.py-4.px-md-4.hov-shadow-md.has-transition.rounded {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(226, 224, 214, 0.6) !important; /* Off-White Border */
            box-shadow: 0 8px 32px 0 rgba(18, 25, 42, 0.04) !important; /* Midnight Navy soft shadow */
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
            border-radius: 20px !important;
        }
        .bg-white.px-3.py-4.px-md-4.hov-shadow-md.has-transition.rounded:hover {
            box-shadow: 0 20px 40px 0 rgba(18, 25, 42, 0.08) !important;
            transform: translateY(-2px) !important;
            border-color: rgba(214, 162, 78, 0.3) !important; /* Warm Gold hover border */
        }

        /* Category navigation upgrades */
        .bg-white.px-3.py-3.border {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(226, 224, 214, 0.6) !important; /* Off-White Border */
            border-radius: 20px !important;
            box-shadow: 0 8px 32px 0 rgba(18, 25, 42, 0.04) !important;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .bg-white.px-3.py-3.border:hover {
            box-shadow: 0 20px 40px 0 rgba(18, 25, 42, 0.08) !important;
            border-color: rgba(214, 162, 78, 0.3) !important;
        }

        /* Premium Product Box Upgrades (Liquid Glass Theme) */
        .aiz-card-box {
            border-radius: 16px !important;
            overflow: hidden !important;
            border: 1px solid rgba(226, 224, 214, 0.5) !important; /* Off-White Border */
            background: #ffffff !important;
            box-shadow: 0 4px 20px rgba(18, 25, 42, 0.02) !important;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
            cursor: pointer !important;
        }
        .aiz-card-box:hover {
            box-shadow: 0 12px 30px rgba(18, 25, 42, 0.08) !important;
            transform: translateY(-4px) !important;
            border-color: rgba(214, 162, 78, 0.25) !important; /* Warm Gold hover border */
        }

        /* Smooth zoom on images */
        .aiz-card-box .img-fit {
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .aiz-card-box:hover .img-fit {
            transform: scale(1.04) !important;
        }

        /* Discount Tag */
        .aiz-card-box .absolute-top-left {
            background: linear-gradient(135deg, #D6A24E 0%, #C98446 100%) !important; /* Warm Gold to Soft Orange */
            border-radius: 20px !important;
            font-family: 'Josefin Sans', sans-serif !important;
            font-weight: 700 !important;
            padding: 3px 10px !important;
            box-shadow: 0 4px 10px rgba(201, 132, 70, 0.25) !important;
            border: none !important;
            color: #ffffff !important;
        }

        /* Quick Action Buttons (Wishlist/Compare) */
        .aiz-card-box .aiz-p-hov-icon {
            gap: 8px !important;
        }
        .aiz-card-box .aiz-p-hov-icon a {
            width: 36px !important;
            height: 36px !important;
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            border-radius: 50% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 4px 12px rgba(18, 25, 42, 0.08) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .aiz-card-box .aiz-p-hov-icon a:hover {
            background: #D6A24E !important; /* Warm Gold */
            transform: scale(1.1) !important;
        }
        .aiz-card-box .aiz-p-hov-icon a:hover svg path {
            fill: #ffffff !important;
        }

        /* Add to Cart Overlay Button */
        .aiz-card-box .cart-btn {
            background: rgba(33, 41, 62, 0.95) !important; /* Dark Slate Blue Glass */
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 30px !important;
            height: 38px !important;
            font-family: 'Josefin Sans', sans-serif !important;
            font-weight: 600 !important;
            letter-spacing: 0.5px !important;
            box-shadow: 0 6px 15px rgba(33, 41, 62, 0.3) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .aiz-card-box .cart-btn:hover {
            background: #C98446 !important; /* Soft Orange */
            box-shadow: 0 8px 20px rgba(201, 132, 70, 0.4) !important;
            transform: scale(1.02) !important;
        }

        /* Flash Deal Nav item glass card */
        .flash-nav-item {
            background: rgba(214, 162, 78, 0.03) !important; /* Warm Gold tint */
            border: 1px solid rgba(214, 162, 78, 0.1) !important;
            border-radius: 16px !important;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .flash-nav-item:hover {
            background: rgba(214, 162, 78, 0.08) !important;
            border-color: rgba(214, 162, 78, 0.3) !important;
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 24px rgba(214, 162, 78, 0.1) !important;
        }

        /* Inspiration Articles Custom styling */
        #home_inspiration_articles_section article {
            border-radius: 16px !important;
            border: 1px solid rgba(226, 224, 214, 0.5) !important; /* Off-White Border */
            background: #ffffff !important;
            box-shadow: 0 4px 20px rgba(18, 25, 42, 0.02) !important;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        #home_inspiration_articles_section article:hover {
            box-shadow: 0 12px 30px rgba(18, 25, 42, 0.08) !important;
            transform: translateY(-4px) !important;
            border-color: rgba(214, 162, 78, 0.15) !important;
        }

        .metro-hero-content {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
            text-shadow: 0 2px 16px rgba(0, 0, 0, .45) !important;
        }
        .metro-hero-title,
        .metro-hero-title * {
            color: #ffffff !important;
        }
        .metro-hero-title {
            font-size: 42px !important;
            line-height: 1.2 !important;
            font-weight: 700 !important;
            letter-spacing: 2px !important;
            margin-bottom: 14px;
            text-shadow: 0 2px 16px rgba(0, 0, 0, .45) !important;
        }
        .metro-hero-description {
            font-size: 16px !important;
            line-height: 1.6 !important;
            color: rgba(255, 255, 255, 0.85) !important;
        }
        .metro-hero-cta {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 46px !important;
            padding: 12px 32px !important;
            border-radius: 30px !important;
            font-weight: 600 !important;
            text-shadow: none !important;
            letter-spacing: 1px !important;
            text-transform: uppercase !important;
            background: linear-gradient(135deg, #D6A24E 0%, #C98446 100%) !important; /* Warm Gold to Soft Orange */
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 10px 20px rgba(201, 132, 70, 0.3) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .metro-hero-cta:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 15px 30px rgba(201, 132, 70, 0.5) !important;
            filter: brightness(1.1) !important;
            color: #ffffff !important;
        }

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
        .metro-marketplace-split {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            width: 100%;
        }
        .metro-marketplace-split-item {
            position: relative;
            min-width: 0;
            height: 480px;
            overflow: hidden;
            background: #f4f4f2;
        }
        .metro-marketplace-split-link,
        .metro-marketplace-split-media {
            position: absolute;
            inset: 0;
            display: block;
        }
        .metro-marketplace-split-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.03);
            transition: transform .4s ease-out;
        }
        .metro-marketplace-split-item:hover .metro-marketplace-split-media img {
            transform: scale(1.065);
        }
        .metro-marketplace-split-content {
            position: relative;
            z-index: 1;
            display: flex;
            width: 100%;
            height: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
            text-shadow: 0 2px 14px rgba(0, 0, 0, .38);
        }
        .metro-marketplace-split-content::before {
            position: absolute;
            inset: 0;
            z-index: -1;
            background: rgba(0, 0, 0, .12);
            content: "";
        }
        .metro-marketplace-split-title {
            color: #ffffff !important;
        }
        .metro-marketplace-split-title {
            display: block;
            max-width: min(100%, 620px);
            font-size: clamp(1.8rem, 3.2vw, 3.1rem);
            line-height: 1.13;
            font-weight: 600;
            color: #ffffff !important;
        }
        .metro-marketplace-split-description {
            display: block;
            max-width: min(100%, 560px);
            margin-top: 14px;
            font-size: 1.05rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, .92) !important;
        }
        .metro-marketplace-split-cta {
            color: #ffffff !important;
            display: inline-block;
            margin-top: 18px;
            border-bottom: 1px solid currentColor;
            padding-bottom: 2px;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.35;
        }
        .metro-collections-split {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            width: 100%;
            background: #f4f4f2;
        }
        .metro-collections-split-panel {
            position: relative;
            min-width: 0;
            min-height: 620px;
            overflow: hidden;
            background-color: #12192a;
            background-position: center;
            background-size: cover;
        }
        .metro-collections-split-panel + .metro-collections-split-panel {
            border-top: 1px solid rgba(18, 25, 42, .12);
        }
        .metro-collections-split-panel::before {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(18, 25, 42, .22) 0%, rgba(18, 25, 42, .5) 55%, rgba(18, 25, 42, .9) 100%);
            content: "";
        }
        .metro-collections-split-panel .metro-collection-subsection {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100%;
            min-height: 620px;
            flex-direction: column;
            justify-content: flex-end;
        }
        .metro-collection-copy {
            padding: 42px 28px 26px;
            text-align: center;
            text-shadow: 0 2px 14px rgba(0, 0, 0, .38);
        }
        .metro-collection-title {
            color: #ffffff !important;
            font-size: clamp(1.75rem, 3vw, 2.75rem);
            line-height: 1.13;
        }
        .metro-collection-description {
            max-width: 560px;
            margin: 12px auto 0;
            color: rgba(255, 255, 255, .92);
            font-size: 1rem;
            line-height: 1.5;
        }
        .metro-collection-cta {
            display: inline-block;
            margin-top: 16px;
            border-bottom: 1px solid currentColor;
            padding-bottom: 2px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.35;
        }
        .metro-collection-products {
            padding: 0 14px 18px;
        }
        .metro-collection-products .slick-list {
            margin: 0 -7px;
        }
        .metro-collection-product-slide {
            padding: 0 7px;
        }
        .metro-collection-product {
            min-width: 0;
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 10px;
            background: rgba(255, 255, 255, .14);
            box-shadow: 0 10px 26px rgba(0, 0, 0, .15);
            color: #ffffff;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: border-color .3s ease, background .3s ease, transform .3s ease;
        }
        .metro-collection-product:hover {
            border-color: rgba(214, 162, 78, .75);
            background: rgba(255, 255, 255, .22);
            transform: translateY(-4px);
        }
        .metro-collection-product-link {
            display: flex;
            height: 100%;
            min-height: 112px;
            align-items: center;
            gap: 10px;
            padding: 12px;
        }
        .metro-collection-product-image {
            width: 68px;
            height: 82px;
            flex: 0 0 68px;
            object-fit: cover;
        }
        .metro-collection-product-name {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            font-size: .8rem;
            line-height: 1.35;
        }
        .metro-collection-product-price {
            display: block;
            margin-top: 6px;
            color: #d6a24e;
            font-size: .85rem;
            font-weight: 700;
        }
        .metro-collection-slider-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 16px;
        }
        .metro-collection-slider-arrow {
            display: inline-flex;
            width: 34px;
            height: 34px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .62);
            border-radius: 50%;
            color: #ffffff;
            transition: background .25s ease, color .25s ease;
        }
        .metro-collection-slider-arrow:hover {
            background: #ffffff;
            color: #12192a !important;
        }
        @media (min-width: 992px) {
            .metro-marketplace-split {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .metro-marketplace-split-item {
                height: 760px;
            }
            .metro-marketplace-split-content {
                padding: 80px;
            }
            .metro-collections-split {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .metro-collections-split-panel + .metro-collections-split-panel {
                border-top: 0;
                border-left: 1px solid rgba(18, 25, 42, .12);
            }
        }
        @media (max-width: 575px) {
            .metro-marketplace-split-content {
                padding: 32px;
            }
            .metro-marketplace-split-title {
                font-size: clamp(1.5rem, 7vw, 2.2rem);
            }
            .metro-marketplace-split-description {
                font-size: .95rem;
            }
            .metro-collections-split-panel,
            .metro-collections-split-panel .metro-collection-subsection {
                min-height: 540px;
            }
            .metro-collection-copy {
                padding: 32px 20px 22px;
            }
            .metro-collection-product-link {
                display: block;
                min-height: 0;
                padding: 8px;
            }
            .metro-collection-product-image {
                width: 100%;
                height: 92px;
            }
            .metro-collection-product-name {
                margin-top: 7px;
                font-size: .72rem;
            }
            .metro-collection-product-price {
                font-size: .76rem;
            }
            .metro-collection-products {
                padding: 0 8px 12px;
            }
            .metro-collection-product-slide {
                padding: 0 5px;
            }
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
        .metro-deferred-section {
            content-visibility: auto;
            contain-intrinsic-size: auto 560px;
        }
    </style>

    @php $lang = get_system_language()->code;  @endphp
    
    <!-- 1. Home Slider -->
    <div class="home-banner-area">
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
                                $slideSrcset = $slider ? uploaded_asset_srcset($slider, ['medium', 'large']) : '';
                            @endphp
                            <div class="carousel-box h-auto">
                                <div class="metro-hero-slide {{ $hasHeroContent ? 'has-content' : '' }} d-block mw-100 img-fit h-180px h-md-320px h-lg-460px h-xl-553px">
                                    @if (!$hasHeroContent && $slideLink)
                                        <a class="d-block h-100" href="{{ $slideLink }}">
                                            <img class="img-fit h-100 m-auto has-transition"
                                            src="{{ $slider ? uploaded_asset($slider, 'large') : static_asset('assets/img/placeholder.jpg') }}"
                                            @if($slideSrcset) srcset="{{ $slideSrcset }}" sizes="100vw" @endif
                                            width="1600" height="720"
                                            loading="{{ $key === 0 ? 'eager' : 'lazy' }}"
                                            @if($key === 0) fetchpriority="high" @endif
                                            alt="{{ $slideTitleText ?: translate('Mayush furniture and decor marketplace promotion') }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                        </a>
                                    @else
                                        <img class="img-fit h-100 m-auto has-transition"
                                        src="{{ $slider ? uploaded_asset($slider, 'large') : static_asset('assets/img/placeholder.jpg') }}"
                                        @if($slideSrcset) srcset="{{ $slideSrcset }}" sizes="100vw" @endif
                                        width="1600" height="720"
                                        loading="{{ $key === 0 ? 'eager' : 'lazy' }}"
                                        @if($key === 0) fetchpriority="high" @endif
                                        alt="{{ $slideTitleText ?: translate('Mayush furniture and decor marketplace promotion') }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                                    @endif
                                    @if ($hasHeroContent)
                                        <div class="metro-hero-content">
                                            @if ($slideTitle)
                                                <h2 class="metro-hero-title">{!! app(\App\Services\HeroTitleSanitizerService::class)->sanitize($slideTitle) !!}</h2>
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
    <div id="todays_deal_section" class="metro-deferred-section" data-section-url="{{ route('home.section.todays_deal') }}">
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
                                    <div class="flash-nav-item img-fit h-100px h-md-140px mb-1 overflow-hidden">
                                        <img draggable="false" class="lazyload img-fit p-3"
                                             src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                             data-src="{{ uploaded_asset($flash_deal_item->banner) }}"
                                             width="140" height="140" loading="lazy" decoding="async"
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
    <div id="section_featured" class="metro-deferred-section" data-section-url="{{ route('home.section.featured') }}">
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

    <!-- 8. Marketplace Banner -->
    @include('frontend.metro.partials.marketplace_banner')

    <!-- 9. Banner Level 2 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner2'])

    <!-- 10. New Collections & Best Selling -->
    @if (get_setting('metro_collections_section_status', '1') == '1')
    @php
        $collectionsLang = get_system_language()->code;
        $newestCollectionImage = uploaded_asset(get_setting('metro_collections_newest_image', null, $collectionsLang)) ?: static_asset('assets/img/placeholder-rect.jpg');
        $bestSellingCollectionImage = uploaded_asset(get_setting('metro_collections_best_selling_image', null, $collectionsLang)) ?: static_asset('assets/img/placeholder-rect.jpg');
    @endphp
    <section id="metro_collections_section" class="metro-collections-split">
        <div id="section_newest" class="metro-collections-split-panel metro-deferred-section" data-section-url="{{ route('home.section.newest_products') }}" data-background-image="{{ $newestCollectionImage }}">
            @include('frontend.metro.partials.collection_panel_placeholder')
        </div>
        <div id="section_best_selling" class="metro-collections-split-panel metro-deferred-section" data-section-url="{{ route('home.section.best_selling') }}" data-background-image="{{ $bestSellingCollectionImage }}">
            @include('frontend.metro.partials.collection_panel_placeholder')
        </div>
    </section>
    @endif

    <!-- 12. Banner Level 3 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner3'])

    @if (get_setting('home_categories_section_status', '1') == '1')
        <!-- 13. Category Wise Products -->
        <div id="section_home_categories" class="metro-deferred-section" data-section-url="{{ route('home.section.home_categories') }}">
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

    <!-- 15. Banner Level 1 -->
    @include('frontend.metro.partials.banner_section', ['banner_key' => 'home_banner1'])

    <!-- 16. Top Sellers (Hidden by default, loaded via AJAX only if criteria met) -->
    <div id="section_best_sellers" class="metro-deferred-section" data-section-url="{{ route('home.section.best_sellers') }}"></div>

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
            var observer = 'IntersectionObserver' in window
                ? new IntersectionObserver(function(entries){
                    entries.forEach(function(entry){
                        if (!entry.isIntersecting) return;
                        observer.unobserve(entry.target);
                        loadSection(entry.target);
                    });
                }, { rootMargin: '500px 0px' })
                : null;

            document.querySelectorAll('[data-section-url]').forEach(function(section){
                if (observer) observer.observe(section);
                else loadSection(section);
            });

            function loadSection(section) {
                var $section = $(section);
                if ($section.data('section-loading')) return;
                $section.data('section-loading', true);

                if (section.dataset.backgroundImage) {
                    section.style.backgroundImage = "url('" + section.dataset.backgroundImage + "')";
                }

                $.get(section.dataset.sectionUrl, function(data){
                    $section.html(data);
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

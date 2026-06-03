@extends('frontend.layouts.app')

@php
    $flashDealsSeoTitle = translate('Flash Deals on Furniture and Decor in Morocco | Mayush');
    $flashDealsSeoDescription = translate('Discover active Mayush flash deals on furniture, decor, lighting and interior design products in Morocco. Shop limited-time offers from marketplace sellers.');
    $flashDealsSeoImage = uploaded_asset(optional($all_flash_deals->first())->banner ?: get_setting('meta_image') ?: get_setting('header_logo'));
    $flashDealsCanonical = route('flash-deals');
    $flashDealsItemList = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $flashDealsSeoTitle,
        'itemListElement' => $flash_deal_products->take(24)->values()->map(function ($flashDealProduct, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => route('product', $flashDealProduct->product->slug),
                'name' => \App\Services\SeoService::altText($flashDealProduct->product->getTranslation('name')),
            ];
        })->all(),
    ];
@endphp

@section('meta_title'){{ $flashDealsSeoTitle }}@stop
@section('meta_description'){{ $flashDealsSeoDescription }}@stop
@section('meta_keywords'){{ translate('flash deals Morocco, furniture deals Morocco, decor offers, interior design promotions, Mayush deals') }}@stop
@section('meta_image'){{ $flashDealsSeoImage }}@stop
@section('canonical_url'){{ $flashDealsCanonical }}@stop

@section('meta')
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::webPageSchema([
        'title' => $flashDealsSeoTitle,
        'description' => $flashDealsSeoDescription,
        'canonical' => $flashDealsCanonical,
    ])) !!}</script>
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::breadcrumbSchema([
        ['name' => translate('Home'), 'url' => route('home')],
        ['name' => translate('Flash Deals'), 'url' => $flashDealsCanonical],
    ])) !!}</script>
    @if($flash_deal_products->isNotEmpty())
        <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd($flashDealsItemList) !!}</script>
    @endif
@endsection

@section('content')
    <style>
        .flash-deals-page {
            background: var(--mayush-white);
            color: var(--mayush-text);
        }
        .flash-deals-page-hero {
            border-bottom: 1px solid var(--mayush-border);
            background: linear-gradient(135deg, var(--mayush-beige) 0%, var(--mayush-beige-alt) 100%);
        }
        .flash-deals-page-hero__inner {
            max-width: 780px;
            padding: 52px 0;
        }
        .flash-deals-eyebrow,
        .flash-deals-count {
            color: var(--mayush-orange);
            font-family: var(--mayush-font-body);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .flash-deals-title,
        .flash-deals-section-title,
        .flash-deal-card__title {
            font-family: var(--mayush-font-heading);
        }
        .flash-deals-title {
            max-width: 720px;
            margin-bottom: 14px;
            color: var(--mayush-black);
            font-size: clamp(38px, 5vw, 62px);
            line-height: 1.08;
        }
        .flash-deals-intro {
            max-width: 700px;
            color: var(--mayush-text-muted);
            font-size: 17px;
            line-height: 1.7;
        }
        .flash-deals-breadcrumb {
            color: var(--mayush-gray);
            font-size: 13px;
        }
        .flash-deals-section-title {
            color: var(--mayush-black);
            font-size: clamp(28px, 3vw, 38px);
        }
        .flash-deals-section-copy {
            max-width: 680px;
            color: var(--mayush-text-muted);
        }
        .flash-deal-card {
            height: 100%;
            overflow: hidden;
            border: 1px solid var(--mayush-border);
            border-radius: var(--mayush-radius-xl);
            background: var(--mayush-white);
            box-shadow: var(--mayush-shadow-card);
            transition: all var(--mayush-transition-base);
        }
        .flash-deal-card:hover {
            border-color: rgba(217, 116, 52, .35);
            box-shadow: var(--mayush-shadow-card-hover);
            transform: translateY(-4px);
        }
        .flash-deal-card__image {
            display: block;
            height: 190px;
            overflow: hidden;
            background: var(--mayush-beige);
        }
        .flash-deal-card__image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--mayush-transition-slow);
        }
        .flash-deal-card:hover .flash-deal-card__image img {
            transform: scale(1.04);
        }
        .flash-deal-card__body {
            padding: 18px;
        }
        .flash-deal-card__title {
            margin-bottom: 8px;
            color: var(--mayush-black);
            font-size: 24px;
        }
        .flash-deal-card__meta {
            color: var(--mayush-gray);
            font-size: 13px;
        }
        .flash-deal-card__link {
            color: var(--mayush-orange);
            font-size: 13px;
            font-weight: 700;
        }
        .flash-deals-empty {
            border: 1px solid var(--mayush-border);
            border-radius: var(--mayush-radius-xl);
            background: var(--mayush-beige);
            padding: 42px 24px;
            text-align: center;
        }
        .flash-deals-empty__icon {
            color: var(--mayush-orange);
            font-size: 48px;
        }
        .flash-deals-products {
            border-top: 1px solid var(--mayush-border);
        }
        @media (max-width: 767px) {
            .flash-deals-page-hero__inner {
                padding: 38px 0;
            }
            .flash-deals-title {
                font-size: 38px;
            }
            .flash-deal-card__image {
                height: 150px;
            }
        }
    </style>

    <div class="flash-deals-page">
        <section class="flash-deals-page-hero">
            <div class="container">
                <div class="flash-deals-page-hero__inner">
                    <div class="flash-deals-breadcrumb mb-3">
                        <a href="{{ route('home') }}" class="text-reset">{{ translate('Home') }}</a>
                        <span class="mx-2">/</span>
                        <span>{{ translate('Flash Deals') }}</span>
                    </div>
                    <div class="flash-deals-eyebrow mb-2">{{ translate('Limited-time offers') }}</div>
                    <h1 class="flash-deals-title">{{ translate('Flash deals on furniture and decor') }}</h1>
                    <p class="flash-deals-intro mb-0">
                        {{ translate('Explore limited-time Mayush offers on furniture, decor, lighting and interior design pieces selected for homes across Morocco.') }}
                    </p>
                </div>
            </div>
        </section>

        @if($all_flash_deals->isNotEmpty())
            <section class="py-4 py-md-5" aria-labelledby="active-flash-deals-title">
                <div class="container">
                    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-4">
                        <div>
                            <div class="flash-deals-count mb-2">{{ $all_flash_deals->count() }} {{ translate('active deals') }}</div>
                            <h2 class="flash-deals-section-title mb-2" id="active-flash-deals-title">{{ translate('Active flash deals') }}</h2>
                            <p class="flash-deals-section-copy mb-0">{{ translate('Choose an offer to discover its curated selection before the promotion ends.') }}</p>
                        </div>
                    </div>

                    <div class="row gutters-10">
                        @foreach($all_flash_deals as $deal)
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <article class="flash-deal-card">
                                    <a href="{{ route('flash-deal-details', $deal->slug) }}" class="flash-deal-card__image">
                                        <img src="{{ uploaded_asset($deal->banner) }}" loading="lazy" decoding="async"
                                            alt="{{ \App\Services\SeoService::altText($deal->getTranslation('title'), translate('Mayush flash deal')) }}"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    </a>
                                    <div class="flash-deal-card__body">
                                        <h3 class="flash-deal-card__title">
                                            <a href="{{ route('flash-deal-details', $deal->slug) }}" class="text-reset">{{ $deal->getTranslation('title') }}</a>
                                        </h3>
                                        <div class="flash-deal-card__meta mb-3">
                                            {{ $deal->flash_deal_products->count() }} {{ translate('products') }}
                                            <span class="mx-2">|</span>
                                            {{ translate('Ends') }} <time datetime="{{ date('c', $deal->end_date) }}">{{ date('d/m/Y H:i', $deal->end_date) }}</time>
                                        </div>
                                        <a href="{{ route('flash-deal-details', $deal->slug) }}" class="flash-deal-card__link">
                                            {{ translate('View deal') }} <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="flash-deals-products py-4 py-md-5" aria-labelledby="flash-deals-products-title">
                <div class="container">
                    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-4">
                        <div>
                            <h2 class="flash-deals-section-title mb-2" id="flash-deals-products-title">{{ translate('Products in our flash deals') }}</h2>
                            <p class="flash-deals-section-copy mb-0">{{ translate('Shop active marketplace promotions with the same product experience used across Mayush.') }}</p>
                        </div>
                        <span class="text-secondary fs-13 mt-2 mt-md-0">{{ $flash_deal_products->count() }} {{ translate('products') }}</span>
                    </div>

                    <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4" id="flash-deals-grid">
                        @include('frontend.flash_deal.partials.product_grid', ['flash_deal_products' => $flash_deal_products])
                    </div>
                </div>
            </section>
        @else
            <section class="py-4 py-md-5">
                <div class="container">
                    <div class="flash-deals-empty">
                        <div class="flash-deals-empty__icon mb-2" aria-hidden="true"><i class="las la-hourglass-half"></i></div>
                        <h2 class="flash-deals-section-title mb-2">{{ translate('New flash deals are coming soon') }}</h2>
                        <p class="flash-deals-section-copy mx-auto mb-3">{{ translate('There are no active flash deals at the moment. Discover popular Mayush products while the next limited-time selection is being prepared.') }}</p>
                        <a href="{{ route('search') }}" class="btn btn-primary px-4">{{ translate('Explore all products') }}</a>
                    </div>
                </div>
            </section>

            @if($fallback_best_sellers->isNotEmpty())
                <section class="flash-deals-products py-4 py-md-5" aria-labelledby="flash-deals-best-sellers-title">
                    <div class="container">
                        <h2 class="flash-deals-section-title mb-2" id="flash-deals-best-sellers-title">{{ translate('Best-selling furniture and decor') }}</h2>
                        <p class="flash-deals-section-copy mb-4">{{ translate('Explore popular products chosen by Mayush customers while you wait for the next flash deal.') }}</p>
                        <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4">
                            @foreach($fallback_best_sellers as $product)
                                <div class="col mb-3">
                                    @include('frontend.metro.partials.product_box_1', ['product' => $product])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if($fallback_suggested->isNotEmpty())
                <section class="flash-deals-products py-4 py-md-5" aria-labelledby="flash-deals-latest-title">
                    <div class="container">
                        <h2 class="flash-deals-section-title mb-2" id="flash-deals-latest-title">{{ translate('Latest interior design arrivals') }}</h2>
                        <p class="flash-deals-section-copy mb-4">{{ translate('Discover recently added furniture and decor from the Mayush marketplace.') }}</p>
                        <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4">
                            @foreach($fallback_suggested as $product)
                                <div class="col mb-3">
                                    @include('frontend.metro.partials.product_box_1', ['product' => $product])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @endif
    </div>
@endsection

@extends('frontend.layouts.app')

@php
    $blogListingTitle = translate('Interior Design Blog and Buying Guides');
    $blogListingDescription = translate('Read Mayush guides for furniture, decor, lighting, home materials, and interior design in Morocco.');
@endphp

@section('meta_title'){{ $blogListingTitle }}@stop
@section('meta_description'){{ $blogListingDescription }}@stop
@section('canonical_url'){{ route('blog') }}@stop

@section('styles')
    <link rel="stylesheet" href="{{ versioned_static_asset('assets/blog/css/blog-conversion.css') }}">
@endsection

@section('meta')
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::webPageSchema([
        'title' => $blogListingTitle,
        'description' => $blogListingDescription,
        'canonical' => route('blog'),
    ])) !!}</script>
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::breadcrumbSchema([
        ['name' => translate('Home'), 'url' => route('home')],
        ['name' => translate('Blog'), 'url' => route('blog')],
    ])) !!}</script>
@endsection

@section('content')
    <section class="pb-4 pt-5 mb-blog mb-blog-listing">
        <div class="container">
            <div class="mb-blog-page-head">
                <div>
                    <ul class="breadcrumb bg-transparent p-0 mb-2">
                        <li class="breadcrumb-item has-transition opacity-60 hov-opacity-100">
                            <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                        </li>
                        <li class="text-dark fw-600 breadcrumb-item">{{ translate('Blog') }}</li>
                    </ul>
                    <h1>{{ $blogListingTitle }}</h1>
                    <p>{{ $blogListingDescription }}</p>
                </div>
                <form id="search-form" action="{{ route('blog') }}" method="GET" class="mb-blog-search">
                    @foreach($selected_categories as $selectedCategory)
                        <input type="hidden" name="selected_categories[]" value="{{ $selectedCategory }}">
                    @endforeach
                    <input type="text" name="search" value="{{ $search }}" placeholder="{{ translate('Search guides') }}" autocomplete="off">
                    <button type="submit" aria-label="{{ translate('Search') }}"><i class="la la-search"></i></button>
                </form>
            </div>

            @if(($blogSettings['hero_enabled'] ?? true) && !empty($featuredBlog))
                @include('frontend.blog.partials.hero', [
                    'blog' => $featuredBlog,
                    'cta' => $blogSettings['hero_cta_text'] ?? translate('Read guide'),
                ])
            @endif

            @if(($blogSettings['category_tabs_enabled'] ?? true) && ($blogCategories ?? collect())->isNotEmpty())
                <nav class="mb-blog-tabs mb-4" aria-label="{{ translate('Blog categories') }}">
                    <a href="{{ route('blog', request()->only('search')) }}" class="{{ empty($selected_categories) ? 'is-active' : '' }}">{{ translate('All') }}</a>
                    @foreach($blogCategories as $category)
                        <a
                            href="{{ route('blog', array_filter(['category' => $category->slug, 'search' => $search])) }}"
                            class="{{ in_array($category->slug, $selected_categories) ? 'is-active' : '' }}">
                            {{ $category->getTranslation() }}
                        </a>
                    @endforeach
                </nav>
            @endif

            <div class="row gutters-16">
                <div class="col-xl-9 order-xl-1">
                    <div class="mb-blog-grid">
                        @foreach($blogs as $blog)
                            @include('frontend.blog.partials.article-card', [
                                'blog' => $blog,
                                'showReadTime' => $blogSettings['read_time_enabled'] ?? true,
                                'showProductCount' => $blogSettings['product_count_badge_enabled'] ?? true,
                            ])
                            @if(($blogSettings['email_listing_inline_enabled'] ?? true) && $loop->iteration % ($blogSettings['email_listing_interval'] ?? 3) === 0)
                                <div class="mb-blog-grid__wide">
                                    @include('frontend.blog.partials.email-card', [
                                        'placement' => 'listing_inline',
                                        'headline' => translate('Plan a better Moroccan home'),
                                        'text' => translate('Get Mayush interior design guides and curated marketplace finds in your inbox.'),
                                    ])
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="aiz-pagination mt-4">
                        {{ $blogs->links() }}
                    </div>
                </div>

                <div class="col-xl-3">
                    @if(($blogSettings['email_sidebar_enabled'] ?? true))
                        @include('frontend.blog.partials.email-card', [
                            'placement' => 'sidebar',
                            'headline' => translate('Design ideas, shoppable finds'),
                            'text' => translate('Get practical room guides and Mayush marketplace picks.'),
                        ])
                    @endif
                    <div class="p-3 border mb-blog-sidebar-block">
                        <h3 class="fs-16 fw-700 text-dark mb-3">{{ translate('Recent Posts') }}</h3>
                        <div class="row">
                            @foreach($recent_blogs as $recent_blog)
                            @php $recentTitle = $recent_blog->getTranslation('title'); @endphp
                            <div class="col-xl-12 col-lg-4 col-sm-6 mb-4 hov-scale-img">
                                <div class="d-flex">
                                    <div class="">
                                        <a href="{{ route('blog.details', $recent_blog->slug) }}" class="text-reset d-block overflow-hidden size-80px size-xl-90px mr-2">
                                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                                data-src="{{ uploaded_asset($recent_blog->banner) }}"
                                                alt="{{ $recentTitle }}"
                                                class="img-fit lazyload h-100 has-transition">
                                        </a>
                                    </div>
                                    <div class="">
                                        <h2 class="fs-14 fw-700 mb-2 mb-xl-3 h-35px text-truncate-2">
                                            <a href="{{ route('blog.details', $recent_blog->slug) }}" class="text-reset hov-text-primary" title="{{ $recentTitle }}">
                                                {{ $recentTitle }}
                                            </a>
                                        </h2>
                                        <div>
                                            <small class="fs-12 fw-400 opacity-60">{{ date('M d, Y',strtotime($recent_blog->created_at)) }}</small>
                                        </div>
                                        @if($recent_blog->category != null)
                                            <div>
                                                <small class="fs-12 fw-400 text-blue">{{ $recent_blog->category->getTranslation() }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script defer src="{{ static_asset('assets/blog/js/blog-conversion.js') }}"></script>
@endsection

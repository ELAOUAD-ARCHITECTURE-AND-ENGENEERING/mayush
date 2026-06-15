@extends('frontend.layouts.app')

@php
    $blogTitle = $blog->getTranslation('title');
    $blogShortDescription = $blog->getTranslation('short_description');
    $blogDescription = $sanitizedBlogDescription ?? $blog->getTranslation('description');
    $blogMetaTitle = $blog->getTranslation('meta_title');
    $blogMetaDescription = $blog->getTranslation('meta_description');
    $blogMetaKeywords = $blog->getTranslation('meta_keywords');
    $blogTitleFallback = $blogTitle . ' - ' . translate('Mayush Interior Design Blog');
    $blogDescriptionFallback = $blogShortDescription ?: strip_tags($blogDescription) ?: ($blogTitle . ' buying guide from Mayush for furniture, decor and interior design in Morocco.');
    $blogSeoTitle = \App\Services\SeoService::meaningfulText($blogMetaTitle ?: $blogTitle, $blogTitleFallback, 70, 30);
    $blogSeoDescription = \App\Services\SeoService::meaningfulText($blogMetaDescription ?: $blogDescriptionFallback, $blogDescriptionFallback, 170, 80);
    $blogSeoImage = uploaded_asset($blog->meta_img ?: $blog->banner);
    $blogCanonical = $blog->canonical_url ?: route('blog.details', $blog->slug);
@endphp

@section('meta_title'){{ $blogSeoTitle }}@stop
@section('meta_description'){{ $blogSeoDescription }}@stop
@section('meta_keywords'){{ $blogMetaKeywords }}@stop
@section('meta_image'){{ $blogSeoImage }}@stop
@section('meta_type')article@stop
@section('canonical_url'){{ $blogCanonical }}@stop

@section('styles')
    <link rel="stylesheet" href="{{ static_asset('assets/blog/css/blog-conversion.css') }}">
@endsection

@section('meta')
    @if($isPreview ?? false)
        <meta name="robots" content="noindex,nofollow">
    @endif
    @if(($blog->schema_enabled ?? true) && ($blogSettings['article_schema_enabled'] ?? true))
        <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::articleSchema($blog)) !!}</script>
    @endif
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::breadcrumbSchema([
        ['name' => translate('Home'), 'url' => route('home')],
        ['name' => translate('Blog'), 'url' => route('blog')],
        ['name' => $blogTitle, 'url' => route('blog.details', $blog->slug)],
    ])) !!}</script>
    @foreach(($blogProductSchemas ?? []) as $blogProductSchema)
        <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd($blogProductSchema) !!}</script>
    @endforeach
@endsection

@section('content')

<section class="py-4 mb-blog mb-blog-detail">
    @if($isPreview ?? false)
        <div class="container">
            <div class="alert alert-info d-flex align-items-center">
                <i class="las la-eye fs-22 mr-2"></i>
                <span>{{ translate('Preview mode: this article is not necessarily visible to readers yet.') }}</span>
            </div>
        </div>
    @endif
    @if(($blogSettings['scroll_progress_enabled'] ?? true))
        <div class="mb-blog-progress" data-blog-scroll-progress></div>
    @endif
    <div class="container">
        <ul class="breadcrumb bg-transparent p-0 mb-3">
            <li class="breadcrumb-item has-transition opacity-60 hov-opacity-100">
                <a class="text-reset" href="{{ route('home') }}">{{ translate('Home') }}</a>
            </li>
            <li class="breadcrumb-item has-transition opacity-60 hov-opacity-100">
                <a class="text-reset" href="{{ route('blog') }}">{{ translate('Blog') }}</a>
            </li>
            <li class="breadcrumb-item fw-600 text-dark">{{ $blogTitle }}</li>
        </ul>
        <div class="row gutters-16 justify-content-center">

            <!-- Blog Details -->
            <div class="col-xxl-7 col-lg-8">
                <div class="mb-4">
                    <header class="mb-blog-article-head">
                        <div class="mb-blog-eyebrow">
                            @if($blog->category != null)
                                <span>{{ $blog->category->category_name }}</span>
                            @endif
                            @if($blog->badge_label)
                                <span>{{ $blog->badge_label }}</span>
                            @endif
                        </div>
                        <h1>{{ $blogTitle }}</h1>
                        @if($blogShortDescription)
                            <p>{{ $blogShortDescription }}</p>
                        @endif
                        <div class="mb-blog-article-head__meta">
                            <span>{{ date('M d, Y', strtotime($blog->published_at ?: $blog->created_at)) }}</span>
                            @if(($blogSettings['read_time_enabled'] ?? true))
                                <span>{{ $blog->read_time_minutes }} {{ translate('min read') }}</span>
                            @endif
                            @if(($blogSettings['product_count_badge_enabled'] ?? true) && $blog->product_count > 0)
                                <span>{{ $blog->product_count }} {{ translate('Mayush picks') }}</span>
                            @endif
                            @if($blog->author)
                                <span>{{ $blog->author->name }}</span>
                            @endif
                        </div>
                    </header>
                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                        data-src="{{ $blog->hero_image_url ?: uploaded_asset($blog->banner) }}"
                        alt="{{ $blogTitle }}"
                        class="mb-blog-article-image lazyload w-100 mt-3 mb-4"
                        loading="lazy">
                    @if(($blogSettings['share_bar_enabled'] ?? true))
                        @include('frontend.blog.partials.share-bar', ['blog' => $blog])
                    @endif
                    <div class="mb-blog-article-body mb-4 overflow-hidden">
                        @include('frontend.blog.partials.content_blocks', [
                            'blocks' => $blog->content_blocks,
                            'fallbackDescription' => $blogDescription
                        ])
                    </div>
                    @if(($blogSettings['product_embeds_enabled'] ?? true))
                        @include('frontend.blog.partials.product-embed', [
                            'products' => ($blogSettings['lazy_product_loading_enabled'] ?? false) ? collect() : ($articleProducts ?? collect()),
                            'title' => translate('Shop this guide'),
                            'subtitle' => translate('Discover Mayush products selected for this article.'),
                            'placement' => 'manual',
                            'blog' => $blog,
                            'count' => $blogSettings['products_per_embed'] ?? 4,
                            'lazy' => $blogSettings['lazy_product_loading_enabled'] ?? false,
                        ])
                    @endif
                    @if(($blogSettings['email_mid_article_enabled'] ?? true))
                        @include('frontend.blog.partials.email-card', [
                            'placement' => 'mid_article',
                            'blog' => $blog,
                            'headline' => translate('Save this inspiration for later'),
                            'text' => translate('Get practical design ideas and curated Mayush product picks in your inbox.'),
                        ])
                    @endif
                    @if(($blogSettings['email_post_read_enabled'] ?? true))
                        @include('frontend.blog.partials.email-card', [
                            'placement' => 'post_read',
                            'blog' => $blog,
                            'headline' => translate('Want more room-by-room ideas?'),
                            'text' => translate('Get Moroccan interior design guides and curated Mayush product picks by email.'),
                        ])
                    @endif
                    @if(($blogSettings['vendor_cta_enabled'] ?? true))
                        @include('frontend.blog.partials.vendor-spotlight', ['blog' => $blog])
                    @endif
                    @if(($blogSettings['product_embeds_enabled'] ?? true) && ($blogSettings['post_read_products_enabled'] ?? true))
                        @include('frontend.blog.partials.product-embed', [
                            'products' => ($blogSettings['lazy_product_loading_enabled'] ?? false) ? collect() : ($postReadProducts ?? collect()),
                            'title' => translate('More products for this look'),
                            'subtitle' => translate('Continue shopping the mood from this guide.'),
                            'placement' => 'post_read',
                            'blog' => $blog,
                            'count' => $blogSettings['post_read_products_count'] ?? 4,
                            'lazy' => $blogSettings['lazy_product_loading_enabled'] ?? false,
                        ])
                    @endif
                    @if(($blogSettings['related_articles_enabled'] ?? true) && ($related_blogs ?? collect())->isNotEmpty())
                        <div class="border-top pt-4 mt-4">
                            <h2 class="fs-18 fw-700 mb-3">{{ translate('Related Posts') }}</h2>
                            <div class="row gutters-10">
                                @foreach($related_blogs as $related_blog)
                                    @php $relatedTitle = $related_blog->getTranslation('title'); @endphp
                                    <div class="col-md-4 mb-3">
                                        <a href="{{ route('blog.details', $related_blog->slug) }}" class="text-reset d-block">
                                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                                data-src="{{ uploaded_asset($related_blog->banner) }}"
                                                alt="{{ $relatedTitle }}"
                                                class="img-fit lazyload h-120px w-100 mb-2">
                                            <span class="fs-14 fw-700 text-truncate-2 d-block">{{ $relatedTitle }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!-- Facebook Comment -->
                    @if (get_setting('facebook_comment') == 1)
                    <div class="mb-4">
                        <div class="fb-comments" data-href="{{ route('blog.details', $blog->slug) }}" data-width="" data-numposts="5"></div>
                    </div>
                    @endif
                </div>
            </div>

            
            <!-- recent posts -->
            <div class="col-xxl-3 col-lg-4">
                <aside class="mb-blog-sidebar" aria-label="{{ translate('Article sidebar') }}">
                    @if(($blogSettings['email_sidebar_enabled'] ?? true))
                        @include('frontend.blog.partials.email-card', [
                            'placement' => 'sidebar',
                            'blog' => $blog,
                            'headline' => translate('Design notes from Mayush'),
                            'text' => translate('Short buying guides, decor inspiration, and marketplace picks for Moroccan homes.'),
                            'button' => translate('Join'),
                        ])
                    @endif
                    @if(($blogSettings['table_of_contents_enabled'] ?? true))
                        @include('frontend.blog.partials.toc', ['toc' => $blogToc ?? []])
                    @endif
                    @if(($blogSettings['product_embeds_enabled'] ?? true) && ($blogSettings['sidebar_products_enabled'] ?? true))
                        @include('frontend.blog.partials.product-embed', [
                            'products' => ($blogSettings['lazy_product_loading_enabled'] ?? false) ? collect() : ($sidebarProducts ?? collect()),
                            'title' => translate('Products in this article'),
                            'subtitle' => null,
                            'placement' => 'sidebar',
                            'blog' => $blog,
                            'count' => $blogSettings['sidebar_products_count'] ?? 3,
                            'lazy' => $blogSettings['lazy_product_loading_enabled'] ?? false,
                        ])
                    @endif
                    <div class="p-3 border mb-blog-sidebar-block">
                        <h3 class="fs-16 fw-700 text-dark mb-3">{{ translate('Recent Posts') }}</h3>
                        <div class="row">
                            @foreach($recent_blogs as $recent_blog)
                            <div class="col-lg-12 col-sm-6 mb-4 hov-scale-img">
                                <div class="d-flex">
                                    <div class="">
                                        @php $recentTitle = $recent_blog->getTranslation('title'); @endphp
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
                                                <small class="fs-12 fw-400 text-blue">{{ $recent_blog->category->category_name }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>

        </div>
    </div>
</section>

@endsection


@section('script')
    <script defer src="{{ static_asset('assets/blog/js/blog-conversion.js') }}"></script>
    @if (get_setting('facebook_comment') == 1)
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0&appId={{ env('FACEBOOK_APP_ID') }}&autoLogAppEvents=1" nonce="ji6tXwgZ"></script>
    @endif
@endsection

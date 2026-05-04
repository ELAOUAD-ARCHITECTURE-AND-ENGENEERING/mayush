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
@endphp

@section('meta_title'){{ $blogSeoTitle }}@stop
@section('meta_description'){{ $blogSeoDescription }}@stop
@section('meta_keywords'){{ $blogMetaKeywords }}@stop
@section('meta_image'){{ $blogSeoImage }}@stop
@section('meta_type', 'article')
@section('canonical_url'){{ route('blog.details', $blog->slug) }}@stop

@section('meta')
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::articleSchema($blog)) !!}</script>
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::breadcrumbSchema([
        ['name' => translate('Home'), 'url' => route('home')],
        ['name' => translate('Blog'), 'url' => route('blog')],
        ['name' => $blogTitle, 'url' => route('blog.details', $blog->slug)],
    ])) !!}</script>
@endsection

@section('content')

<section class="py-4">
    <div class="container">
        <div class="row gutters-16 justify-content-center">

            <!-- Blog Details -->
            <div class="col-xxl-7 col-lg-8">
                <div class="mb-4">
                    <!-- Title -->
                    <h1 class="fs-20 fs-md-24 fw-700 mb-3">
                        <a href="{{ route('blog.details', $blog->slug) }}" class="text-reset hov-text-primary" title="{{ $blogTitle }}">
                            {{ $blogTitle }}
                        </a>
                    </h1>
                    <div class="row">
                        <div class="col-4">
                            <!-- Date -->
                            <div>
                                <small class="fs-12 fw-400 opacity-60">{{ date('M d, Y',strtotime($blog->created_at)) }}</small>
                            </div>
                            <!-- Caregory -->
                            @if($blog->category != null)
                                <div>
                                    <small class="fs-12 fw-400 text-blue">{{ $blog->category->category_name }}</small>
                                </div>
                            @endif
                            @if($blog->author != null)
                                <div>
                                    <small class="fs-12 fw-400 opacity-60">{{ translate('By') }} {{ $blog->author->name }}</small>
                                </div>
                            @endif
                        </div>
                        <!-- Share -->
                        <div class="col-8 text-right">
                            <div class="aiz-share"></div>
                        </div>
                    </div>
                    <!-- Image -->
                    <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                        data-src="{{ uploaded_asset($blog->banner) }}"
                        alt="{{ $blogTitle }}"
                        class="img-fluid lazyload w-100 mt-3 mb-4">
                    <!-- Description -->
                    <div class="mb-4 overflow-hidden">
                        {!! $blogDescription !!}
                    </div>
                    @include('frontend.blog.partials.product-embed', [
                        'products' => $articleProducts ?? collect(),
                        'title' => translate('Shop this guide'),
                        'subtitle' => translate('Discover Mayush products selected for this article.'),
                        'placement' => 'article',
                    ])
                    @include('frontend.blog.partials.email-card', [
                        'placement' => 'post_read',
                        'blog' => $blog,
                        'headline' => translate('Want more room-by-room ideas?'),
                        'text' => translate('Get Moroccan interior design guides and curated Mayush product picks by email.'),
                    ])
                    @if(($related_blogs ?? collect())->isNotEmpty())
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
                        <div class="fb-comments" data-href="{{ route("blog",$blog->slug) }}" data-width="" data-numposts="5"></div>
                    </div>
                    @endif
                </div>
            </div>

            
            <!-- recent posts -->
            <div class="col-xxl-3 col-lg-4">
                @include('frontend.blog.partials.email-card', [
                    'placement' => 'sidebar',
                    'blog' => $blog,
                    'headline' => translate('Design notes from Mayush'),
                    'text' => translate('Short buying guides, decor inspiration, and marketplace picks for Moroccan homes.'),
                    'button' => translate('Join'),
                ])
                <div class="p-3 border">
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
            </div>

        </div>
    </div>
</section>

@endsection


@section('script')
    @if (get_setting('facebook_comment') == 1)
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0&appId={{ env('FACEBOOK_APP_ID') }}&autoLogAppEvents=1" nonce="ji6tXwgZ"></script>
    @endif
@endsection

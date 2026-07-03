@php
    $title = $blog->getTranslation('title');
    $summary = $blog->getTranslation('short_description');
    $image = $blog->hero_image_url ?: uploaded_asset($blog->banner);
    $showReadTime = $showReadTime ?? true;
    $showProductCount = $showProductCount ?? true;
@endphp

<article class="mb-blog-article-card">
    <a href="{{ route('blog.details', $blog->slug) }}" class="mb-blog-article-card__image text-reset">
        <img
            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
            data-src="{{ $image }}"
            alt="{{ $title }}"
            class="lazyload"
            loading="lazy">
    </a>
    <div class="mb-blog-article-card__body">
        <div class="mb-blog-eyebrow">
            @if($blog->category)
                <span>{{ $blog->category->category_name }}</span>
            @endif
            @if($blog->badge_label)
                <span>{{ $blog->badge_label }}</span>
            @endif
        </div>
        <h2>
            <a href="{{ route('blog.details', $blog->slug) }}" class="text-reset">{{ $title }}</a>
        </h2>
        @if($summary)
            <p>{{ $summary }}</p>
        @endif
        <div class="mb-blog-article-card__footer">
            <div>
                @if($showReadTime)
                    <span>{{ $blog->read_time_minutes }} {{ translate('min read') }}</span>
                @endif
                @if($showProductCount && $blog->product_count > 0)
                    <span>{{ $blog->product_count }} {{ translate('products') }}</span>
                @endif
            </div>
            <a href="{{ route('blog.details', $blog->slug) }}" class="mb-blog-link">{{ translate('Read guide') }}</a>
        </div>
    </div>
</article>

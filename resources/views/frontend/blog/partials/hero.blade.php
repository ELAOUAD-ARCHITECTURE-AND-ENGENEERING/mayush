@if(!empty($blog))
    @php
        $title = $blog->getTranslation('title');
        $summary = $blog->getTranslation('short_description');
        $image = $blog->hero_image_url ?: uploaded_asset($blog->banner);
        $cta = $cta ?? translate('Read guide');
    @endphp
    <section class="mb-blog-hero mb-4">
        <a href="{{ route('blog.details', $blog->slug) }}" class="mb-blog-hero__media d-block text-reset">
            <img
                src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                data-src="{{ $image }}"
                alt="{{ $title }}"
                class="lazyload"
                loading="lazy">
        </a>
        <div class="mb-blog-hero__content">
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
            <div class="mb-blog-hero__meta">
                <span>{{ $blog->read_time_minutes }} {{ translate('min read') }}</span>
                @if($blog->product_count > 0)
                    <span>{{ $blog->product_count }} {{ translate('Mayush picks') }}</span>
                @endif
            </div>
            <a href="{{ route('blog.details', $blog->slug) }}" class="mb-blog-link">
                {{ $cta }}
                <i class="las la-arrow-right"></i>
            </a>
        </div>
    </section>
@endif

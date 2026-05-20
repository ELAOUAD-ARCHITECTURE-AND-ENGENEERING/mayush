@php
    $inspirationBlogs = $inspiration_blogs ?? collect();
@endphp

@if($inspirationBlogs->isNotEmpty())
    <section id="home_inspiration_articles_section" class="mb-4 mt-3 mt-md-4">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="fs-22 fs-md-28 fw-800 mb-0 text-dark">{{ translate('Inspiration & Conseils') }}</h2>
                <a href="{{ route('blog') }}" class="fs-13 fw-700 text-dark hov-text-primary">
                    {{ translate('read more') }}
                </a>
            </div>
            <div class="aiz-carousel inspiration-articles-carousel gutters-16"
                data-items="3" data-xl-items="3" data-lg-items="3" data-md-items="2"
                data-sm-items="2" data-xs-items="1" data-arrows="true" data-dots="false"
                data-infinite="{{ $inspirationBlogs->count() > 3 ? 'true' : 'false' }}">
                @foreach($inspirationBlogs->take(6) as $blog)
                    @php
                        $title = $blog->getTranslation('title');
                        $summary = $blog->getTranslation('short_description');
                        $image = $blog->hero_image ?: $blog->banner ?: $blog->meta_img;
                    @endphp
                    <div class="carousel-box pb-2">
                        <article class="h-100 bg-white border rounded overflow-hidden has-transition hov-shadow-md">
                            <a href="{{ route('blog.details', $blog->slug) }}" class="d-block overflow-hidden h-180px hov-scale-img">
                                <img
                                    src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ $image ? uploaded_asset($image) : static_asset('assets/img/placeholder-rect.jpg') }}"
                                    alt="{{ $title }}"
                                    class="img-fit lazyload h-100 w-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                >
                            </a>
                            <div class="p-3">
                                <div class="fs-12 text-secondary mb-2">
                                    {{ optional($blog->published_at ? \Carbon\Carbon::parse($blog->published_at) : $blog->created_at)->format('M d, Y') }}
                                    @if($blog->category)
                                        <span class="mx-1">|</span>{{ $blog->category->category_name }}
                                    @endif
                                </div>
                                <h3 class="fs-16 fw-700 mb-2 text-truncate-2" style="min-height: 42px;">
                                    <a href="{{ route('blog.details', $blog->slug) }}" class="text-dark hov-text-primary">{{ $title }}</a>
                                </h3>
                                @if($summary)
                                    <p class="fs-14 text-secondary mb-0 text-truncate-2" style="min-height: 42px;">{{ $summary }}</p>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@php
    $homeBlogs = $latest_blogs ?? collect();
@endphp

@if($homeBlogs->isNotEmpty())
    <section class="mb-4">
        <div class="container">
            <div class="d-flex mb-3 align-items-baseline border-bottom">
                <h2 class="h5 fw-700 mb-0">
                    <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Latest from Blog') }}</span>
                </h2>
                <a href="{{ route('blog') }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View All') }}</a>
            </div>
            <div class="row gutters-10">
                @foreach($homeBlogs as $blog)
                    @php
                        $title = $blog->getTranslation('title');
                        $summary = $blog->getTranslation('short_description');
                    @endphp
                    <div class="col-lg-4 col-md-6 mb-3">
                        <article class="bg-white border h-100 rounded overflow-hidden hov-shadow-md">
                            <a href="{{ route('blog.details', $blog->slug) }}" class="d-block overflow-hidden h-180px">
                                <img
                                    src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($blog->banner) }}"
                                    alt="{{ $title }}"
                                    class="img-fit lazyload h-100 w-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                >
                            </a>
                            <div class="p-3">
                                <div class="fs-12 opacity-60 mb-2">
                                    {{ optional($blog->published_at ? \Carbon\Carbon::parse($blog->published_at) : $blog->created_at)->format('M d, Y') }}
                                    @if($blog->category)
                                        <span class="mx-1">|</span>{{ $blog->category->getTranslation() }}
                                    @endif
                                </div>
                                <h3 class="fs-16 fw-700 mb-2 h-40px text-truncate-2">
                                    <a href="{{ route('blog.details', $blog->slug) }}" class="text-reset hov-text-primary">{{ $title }}</a>
                                </h3>
                                <p class="fs-14 opacity-70 mb-0 h-45px text-truncate-2">{{ $summary }}</p>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if(!empty($blog) && ($blog->shop || $blog->vendor_quote))
    <section class="mb-blog-vendor-spotlight border bg-white p-4 my-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div>
                <span class="fs-12 fw-700 text-primary text-uppercase">{{ translate('Vendor spotlight') }}</span>
                <h2 class="fs-18 fw-700 mb-2">
                    {{ optional($blog->shop)->name ?: translate('Mayush Design') }}
                </h2>
                @if($blog->vendor_quote)
                    <blockquote class="mb-0 opacity-80">"{{ $blog->vendor_quote }}"</blockquote>
                @endif
            </div>
            @if($blog->shop && $blog->shop->slug)
                <a href="{{ route('shop.visit', $blog->shop->slug) }}" class="btn btn-soft-primary mt-3 mt-md-0">
                    {{ translate('Visit store') }}
                </a>
            @endif
        </div>
    </section>
@endif

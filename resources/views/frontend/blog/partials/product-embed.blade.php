@php
    $products = $products ?? collect();
    $lazy = $lazy ?? false;
    $count = $count ?? 4;
    $placement = $placement ?? 'manual';
    $blog = $blog ?? null;
    $isSidebar = $placement === 'sidebar';
    $columnClass = $isSidebar ? 'col-12' : 'col-md-6';
@endphp

@if($products->isNotEmpty() || ($lazy && !empty($blog)))
    <section
        class="mb-blog-product-embed {{ $isSidebar ? 'mb-blog-product-embed--sidebar' : 'border-top border-bottom py-4 my-4' }}"
        data-blog-product-placement="{{ $placement }}"
        @if($lazy && !empty($blog))
            data-blog-products-lazy
            data-blog-id="{{ $blog->id }}"
            data-blog-count="{{ $count }}"
            data-blog-placement="{{ $placement }}"
            data-blog-products-url="{{ url('/api/blog/products') }}"
        @endif>
        <div class="mb-blog-product-embed__head d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-3">
            <div>
                @if($isSidebar)
                    <h3 class="fs-16 fw-700 mb-1">{{ $title ?? translate('Shop this guide') }}</h3>
                @else
                    <h2 class="fs-18 fw-700 mb-1">{{ $title ?? translate('Shop this guide') }}</h2>
                @endif
                @if(!empty($subtitle))
                    <p class="fs-14 opacity-70 mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            @unless($isSidebar)
                <span class="fs-12 fw-700 text-primary mt-2 mt-md-0">{{ translate('Curated products') }}</span>
            @endunless
        </div>
        <div class="row gutters-10" data-blog-products-target>
            @if($lazy && !empty($blog))
                @for($i = 0; $i < $count; $i++)
                    <div class="{{ $columnClass }} mb-3">
                        <div class="mb-blog-product-skeleton"></div>
                    </div>
                @endfor
            @else
                @foreach($products as $product)
                    <div class="{{ $columnClass }} mb-3">
                        @include('frontend.blog.partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            @endif
        </div>
    </section>
@endif

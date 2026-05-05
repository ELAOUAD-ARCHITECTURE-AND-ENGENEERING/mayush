@php
    $products = $products ?? collect();
    $lazy = $lazy ?? false;
    $count = $count ?? 4;
    $placement = $placement ?? 'manual';
    $blog = $blog ?? null;
@endphp

@if($products->isNotEmpty() || ($lazy && !empty($blog)))
    <section
        class="mb-blog-product-embed border-top border-bottom py-4 my-4"
        data-blog-product-placement="{{ $placement }}"
        @if($lazy && !empty($blog))
            data-blog-products-lazy
            data-blog-id="{{ $blog->id }}"
            data-blog-count="{{ $count }}"
            data-blog-placement="{{ $placement }}"
            data-blog-products-url="{{ url('/api/blog/products') }}"
        @endif>
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-3">
            <div>
                <h2 class="fs-18 fw-700 mb-1">{{ $title ?? translate('Shop this guide') }}</h2>
                @if(!empty($subtitle))
                    <p class="fs-14 opacity-70 mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            <span class="fs-12 fw-700 text-primary mt-2 mt-md-0">{{ translate('Curated products') }}</span>
        </div>
        <div class="row gutters-10" data-blog-products-target>
            @if($lazy && !empty($blog))
                @for($i = 0; $i < $count; $i++)
                    <div class="col-md-6 mb-3">
                        <div class="mb-blog-product-skeleton"></div>
                    </div>
                @endfor
            @else
                @foreach($products as $product)
                    <div class="col-md-6 mb-3">
                        @include('frontend.blog.partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            @endif
        </div>
    </section>
@endif

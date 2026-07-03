@extends('frontend.layouts.app')

@php
    $metaTitle = $collection->meta_title ?: $collection->name;
    $metaDescription = $collection->meta_description ?: $collection->description;
    $metaImage = uploaded_asset($collection->meta_image ?: $collection->hero_image);
@endphp

@section('meta_title'){{ $metaTitle }}@stop
@section('meta_description'){{ $metaDescription }}@stop
@section('meta_image'){{ $metaImage }}@stop
@section('canonical_url'){{ route('product-collections.show', $collection->slug) }}@stop

@section('content')
    <style>
        .collection-page-header {
            position: relative;
            overflow: hidden;
            background: #12192a;
            background-position: center;
            background-size: cover;
        }
        .collection-page-header::before {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(18, 25, 42, .92), rgba(18, 25, 42, .55));
            content: "";
        }
        .collection-page-header-content {
            position: relative;
            z-index: 1;
            max-width: 760px;
            padding: 38px 0;
        }
        .collection-page-title {
            color: #fff;
            font-size: clamp(1.8rem, 4vw, 3rem);
        }
        .collection-page-description {
            display: -webkit-box;
            max-width: 680px;
            overflow: hidden;
            color: rgba(255, 255, 255, .88);
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .collection-recommendations {
            border-top: 1px solid #e7e7e7;
        }
    </style>

    <section class="collection-page-header" style="@if($metaImage) background-image: url('{{ $metaImage }}'); @endif">
        <div class="container">
            <div class="collection-page-header-content text-white">
                <div class="fs-13 mb-2 opacity-70">
                    <a href="{{ route('home') }}" class="text-reset">{{ translate('Home') }}</a>
                    <span class="mx-2">/</span>
                    <span>{{ translate('Collections') }}</span>
                </div>
                <h1 class="collection-page-title fw-700 mb-2">{{ $collection->name }}</h1>
                @if ($collection->description)
                    <p class="collection-page-description fs-15 mb-0">{{ $collection->description }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="py-4 py-md-5">
        <div class="container">
            <form method="GET" action="{{ route('product-collections.show', $collection->slug) }}" class="bg-white border p-3 mb-4">
                <div class="row gutters-10 align-items-end">
                    <div class="col-md-4">
                        <label class="fs-12 fw-700 text-uppercase">{{ translate('Search in collection') }}</label>
                        <input type="text" class="form-control" name="keyword" value="{{ request('keyword') }}" placeholder="{{ translate('Search products') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="fs-12 fw-700 text-uppercase">{{ translate('Brand') }}</label>
                        <select class="form-control aiz-selectpicker" name="brand">
                            <option value="">{{ translate('All brands') }}</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected((string) request('brand') === (string) $brand->id)>{{ $brand->getTranslation('name') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="fs-12 fw-700 text-uppercase">{{ translate('Min price') }}</label>
                        <input type="number" min="0" class="form-control" name="min_price" value="{{ request('min_price') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="fs-12 fw-700 text-uppercase">{{ translate('Max price') }}</label>
                        <input type="number" min="0" class="form-control" name="max_price" value="{{ request('max_price') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="fs-12 fw-700 text-uppercase">{{ translate('Sort by') }}</label>
                        <select class="form-control aiz-selectpicker" name="sort_by">
                            @foreach ([
                                'newest' => translate('Newest'),
                                'popular' => translate('Popularity'),
                                'price-asc' => translate('Price low to high'),
                                'price-desc' => translate('Price high to low'),
                                'oldest' => translate('Oldest'),
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('product-collections.show', $collection->slug) }}" class="btn btn-soft-secondary mr-2">{{ translate('Clear') }}</a>
                    <button class="btn btn-primary">{{ translate('Apply filters') }}</button>
                </div>
            </form>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="fs-20 fw-700 mb-0">{{ translate('Collection Products') }}</h2>
                <span class="fs-13 text-secondary">{{ $products->total() }} {{ translate('products') }}</span>
            </div>

            <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4">
                @forelse ($products as $product)
                    <div class="col mb-3">
                        @include('frontend.metro.partials.product_box_1', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="bg-white border p-4 text-center text-secondary">{{ translate('No products match this collection.') }}</div>
                    </div>
                @endforelse
            </div>

            <div class="aiz-pagination aiz-pagination-center mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </section>

    @include('frontend.product_collections.partials.recommendation_row', [
        'sectionId' => 'collection_best_selling_products',
        'title' => translate('Most Buying Products'),
        'products' => $bestSellingProducts,
    ])

    @auth
        @include('frontend.product_collections.partials.recommendation_row', [
            'sectionId' => 'collection_recently_viewed_products',
            'title' => translate('Recently Viewed Products'),
            'products' => $recentlyViewedProducts,
        ])
    @endauth
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            AIZ.plugins.slickCarousel();
        });
    </script>
@endsection

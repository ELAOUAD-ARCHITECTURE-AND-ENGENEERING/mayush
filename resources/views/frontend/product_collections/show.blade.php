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

        /* Floating label price input box (Matching Reference Design) */
        .price-input-box {
            position: relative;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 6px 12px;
            transition: all 0.2s ease;
        }
        .price-input-box:focus-within {
            border-color: #d36d28;
            box-shadow: 0 0 0 3px rgba(211, 109, 40, 0.15);
        }
        .price-input-box label {
            position: absolute;
            top: -9px;
            left: 10px;
            background: #ffffff;
            padding: 0 5px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.03em;
        }
        .price-input-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }
        .price-input-box span.currency {
            font-weight: 600;
            color: #64748b;
            font-size: 13px;
        }

        /* Dual Range Slider Track & Handles */
        .dual-range-wrapper {
            position: relative;
            width: 100%;
            height: 24px;
            margin-top: 6px;
        }
        .dual-range-track {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 0;
            right: 0;
            height: 6px;
            background: #f1f5f9;
            border-radius: 3px;
            overflow: hidden;
        }
        .dual-range-fill {
            position: absolute;
            top: 0;
            bottom: 0;
            background: linear-gradient(90deg, #e27227, #d36d28);
            border-radius: 3px;
        }
        .dual-range-input {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 0;
            width: 100%;
            margin: 0;
            appearance: none;
            -webkit-appearance: none;
            background: none;
            pointer-events: none;
            z-index: 3;
        }
        .dual-range-input::-webkit-slider-thumb {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #d36d28;
            box-shadow: 0 2px 6px rgba(0,0,0,0.18);
            cursor: pointer;
            pointer-events: auto;
            transition: transform 0.15s ease;
        }
        .dual-range-input::-webkit-slider-thumb:hover {
            transform: scale(1.15);
        }
        .dual-range-input::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #d36d28;
            box-shadow: 0 2px 6px rgba(0,0,0,0.18);
            cursor: pointer;
            pointer-events: auto;
            transition: transform 0.15s ease;
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

    <section class="py-4 py-md-5 position-relative z-1">
        <div class="container">
            <form method="GET" action="{{ route('product-collections.show', $collection->slug) }}" class="bg-white rounded border shadow-sm p-4 mb-4">
                <div class="row gutters-15 align-items-center">
                    <!-- Search Field -->
                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                        <label class="fs-12 fw-700 text-uppercase text-secondary mb-2">{{ translate('Search in collection') }}</label>
                        <input type="text" class="form-control rounded-lg" name="keyword" value="{{ request('keyword') }}" placeholder="{{ translate('Search products') }}...">
                    </div>

                    <!-- Dual Price Range Selector (Sophisticated Component) -->
                    <div class="col-lg-5 col-md-12 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="fs-12 fw-700 text-uppercase text-secondary mb-0">{{ translate('Price') }}</label>
                            <span class="fs-12 text-muted fw-600"><span id="min-price-val">0</span> DH — <span id="max-price-val">{{ $maxPriceLimit }}</span> DH</span>
                        </div>

                        <!-- Floating Input Cards -->
                        <div class="d-flex align-items-center gap-2 mb-2" style="gap: 12px;">
                            <div class="price-input-box flex-grow-1">
                                <label>{{ translate('Min.') }}</label>
                                <div class="d-flex align-items-center">
                                    <input type="number" id="min_price_input" name="min_price" value="{{ request('min_price', $minPriceLimit) }}" min="{{ $minPriceLimit }}" max="{{ $maxPriceLimit }}" step="1">
                                    <span class="currency">DH</span>
                                </div>
                            </div>
                            <span class="text-muted fw-700">—</span>
                            <div class="price-input-box flex-grow-1">
                                <label>{{ translate('Max.') }}</label>
                                <div class="d-flex align-items-center">
                                    <input type="number" id="max_price_input" name="max_price" value="{{ request('max_price', $maxPriceLimit) }}" min="{{ $minPriceLimit }}" max="{{ $maxPriceLimit }}" step="1">
                                    <span class="currency">DH</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dual Range Slider Controls -->
                        <div class="dual-range-wrapper">
                            <div class="dual-range-track">
                                <div class="dual-range-fill" id="dual-range-fill"></div>
                            </div>
                            <input type="range" id="min_range" min="{{ $minPriceLimit }}" max="{{ $maxPriceLimit }}" value="{{ request('min_price', $minPriceLimit) }}" step="1" class="dual-range-input">
                            <input type="range" id="max_range" min="{{ $minPriceLimit }}" max="{{ $maxPriceLimit }}" value="{{ request('max_price', $maxPriceLimit) }}" step="1" class="dual-range-input">
                        </div>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <label class="fs-12 fw-700 text-uppercase text-secondary mb-2">{{ translate('Sort by') }}</label>
                        <select class="form-control aiz-selectpicker rounded-lg" name="sort_by">
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

                <!-- Action Bar -->
                <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                    <a href="{{ route('product-collections.show', $collection->slug) }}" class="btn btn-soft-secondary px-4 mr-2">{{ translate('Clear') }}</a>
                    <button type="submit" class="btn btn-primary px-4 fw-600">{{ translate('Apply filters') }}</button>
                </div>
            </form>

            <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
                <h2 class="fs-20 fw-700 mb-0">{{ translate('Collection Products') }}</h2>
                <span class="fs-13 text-secondary fw-500">{{ $products->total() }} {{ translate('products') }}</span>
            </div>

            <div class="row gutters-10 row-cols-2 row-cols-md-3 row-cols-lg-4 pt-2">
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

        document.addEventListener('DOMContentLoaded', function () {
            const minRange = document.getElementById('min_range');
            const maxRange = document.getElementById('max_range');
            const minInput = document.getElementById('min_price_input');
            const maxInput = document.getElementById('max_price_input');
            const minValLabel = document.getElementById('min-price-val');
            const maxValLabel = document.getElementById('max-price-val');
            const rangeFill = document.getElementById('dual-range-fill');

            if (!minRange || !maxRange) return;

            const minLimit = parseInt(minRange.min);
            const maxLimit = parseInt(minRange.max);

            function updateFill() {
                let minVal = parseInt(minRange.value);
                let maxVal = parseInt(maxRange.value);

                if (isNaN(minVal)) minVal = minLimit;
                if (isNaN(maxVal)) maxVal = maxLimit;

                if (minVal > maxVal) {
                    let tmp = minVal;
                    minVal = maxVal;
                    maxVal = tmp;
                }

                const rangeSpan = (maxLimit - minLimit) || 1;
                const pctMin = Math.max(0, Math.min(100, ((minVal - minLimit) / rangeSpan) * 100));
                const pctMax = Math.max(0, Math.min(100, ((maxVal - minLimit) / rangeSpan) * 100));

                rangeFill.style.left = pctMin + '%';
                rangeFill.style.width = Math.max(0, pctMax - pctMin) + '%';

                minInput.value = minVal;
                maxInput.value = maxVal;
                if (minValLabel) minValLabel.textContent = minVal;
                if (maxValLabel) maxValLabel.textContent = maxVal;
            }

            minRange.addEventListener('input', function () {
                if (parseInt(minRange.value) > parseInt(maxRange.value)) {
                    minRange.value = maxRange.value;
                }
                updateFill();
            });

            maxRange.addEventListener('input', function () {
                if (parseInt(maxRange.value) < parseInt(minRange.value)) {
                    maxRange.value = minRange.value;
                }
                updateFill();
            });

            minInput.addEventListener('change', function () {
                let val = parseInt(minInput.value) || minLimit;
                val = Math.max(minLimit, Math.min(val, parseInt(maxInput.value)));
                minRange.value = val;
                updateFill();
            });

            maxInput.addEventListener('change', function () {
                let val = parseInt(maxInput.value) || maxLimit;
                val = Math.min(maxLimit, Math.max(val, parseInt(minInput.value)));
                maxRange.value = val;
                updateFill();
            });

            updateFill();
        });
    </script>
@endsection

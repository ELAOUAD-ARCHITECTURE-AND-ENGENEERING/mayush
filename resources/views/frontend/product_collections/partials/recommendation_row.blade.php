@if ($products->isNotEmpty())
    <section id="{{ $sectionId }}" class="collection-recommendations py-4 py-md-5">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="fs-20 fs-md-24 fw-700 mb-0">{{ $title }}</h2>
                <div>
                    <a type="button" class="arrow-prev slide-arrow link-disable text-secondary mr-2" onclick="clickToSlide('slick-prev','{{ $sectionId }}')">
                        <i class="las la-angle-left fs-20 fw-600"></i>
                    </a>
                    <a type="button" class="arrow-next slide-arrow text-secondary" onclick="clickToSlide('slick-next','{{ $sectionId }}')">
                        <i class="las la-angle-right fs-20 fw-600"></i>
                    </a>
                </div>
            </div>
            <div class="aiz-carousel gutters-10 arrow-inactive-none" data-items="6" data-xl-items="5" data-lg-items="4" data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows="false" data-infinite="false">
                @foreach ($products as $product)
                    <div class="carousel-box px-1">
                        @include('frontend.metro.partials.product_box_1', ['product' => $product])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

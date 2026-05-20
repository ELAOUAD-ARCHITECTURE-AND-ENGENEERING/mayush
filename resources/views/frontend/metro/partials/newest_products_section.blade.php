<div class="metro-collection-subsection">
    <div class="d-flex mb-2 mb-md-3 align-items-start justify-content-between">
        <div class="pr-3">
            <h2 class="fs-18 fs-md-24 fw-800 mb-1 text-dark">{{ translate('Nouvelles collections') }}</h2>
            <h3 class="fs-13 fs-md-15 fw-400 text-muted mb-0 lh-1-6">
                {{ translate('Découvrez une sélection exclusive de mobilier et décoration où design contemporain, confort et raffinement se rencontrent.') }}
            </h3>
        </div>
        <div class="d-flex flex-shrink-0 align-items-center">
            <a type="button" class="arrow-prev slide-arrow link-disable text-secondary mr-2" onclick="clickToSlide('slick-prev','section_newest')"><i class="las la-angle-left fs-20 fw-600"></i></a>
            <a class="text-blue fs-10 fs-md-12 fw-700 hov-text-primary animate-underline-primary" href="{{ route('search',['sort_by'=>'newest']) }}">{{ translate('View All') }}</a>
            <a type="button" class="arrow-next slide-arrow text-secondary ml-2" onclick="clickToSlide('slick-next','section_newest')"><i class="las la-angle-right fs-20 fw-600"></i></a>
        </div>
    </div>
    <div class="px-sm-3">
        <div class="aiz-carousel arrow-inactive-none sm-gutters-16" data-items="6" data-xxl-items="6" data-xl-items="6" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true' data-infinite='false' data-autoplay='true'>
            @if (count($newest_products) > 0)
                @foreach ($newest_products as $key => $new_product)
                <div class="carousel-box px-3 position-relative has-transition border-right border-top border-bottom @if($key == 0) border-left @endif hov-animate-outline">
                    @include('frontend.'.get_setting('homepage_select').'.partials.product_box_2',['product' => $new_product])
                </div>
                @endforeach
            @else
                @for ($i = 0; $i < 6; $i++)
                <div class="carousel-box px-3 position-relative has-transition border-right border-top border-bottom @if($i == 0) border-left @endif hov-animate-outline">
                    @include('frontend.'.get_setting('homepage_select').'.partials.product_placeholder_box')
                </div>
                @endfor
            @endif
        </div>
    </div>
</div>

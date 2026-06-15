@if (isset($elite_shops) && count($elite_shops) > 0)
<section class="mb-4">
    <div class="container">
        <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded border-warning" style="border: 2px solid #f1c40f;">
            <div class="d-flex mb-3 align-items-baseline border-bottom">
                <h3 class="h5 fw-700 mb-0">
                    <span class="border-bottom border-warning border-width-2 pb-3 d-inline-block text-warning" style="color: #c9a13b !important;">
                        <i class="las la-crown"></i> {{ translate('Elite Artisans Showcase') }}
                    </span>
                </h3>
            </div>
            
            <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="4" data-xl-items="4" data-lg-items="3" data-md-items="2" data-sm-items="1" data-xs-items="1" data-arrows='true'>
                @foreach ($elite_shops as $shop)
                    <div class="carousel-box">
                        <div class="aiz-card-box rounded hov-shadow-md mt-1 mb-2 has-transition bg-white">
                            <div class="position-relative">
                                <a href="{{ route('shop.visit', $shop->slug) }}" class="d-block">
                                    <img
                                        class="img-fit lazyload mx-auto h-210px"
                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                        data-src="{{ uploaded_asset($shop->logo) }}"
                                        alt="{{  $shop->name  }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                    >
                                </a>
                                @if($shop->story_title)
                                    <div class="absolute-bottom-left fs-12 text-white bg-dark px-2 py-1 opacity-70">
                                        {{ Str::limit($shop->story_title, 35) }}
                                    </div>
                                @endif
                            </div>
                            <div class="p-md-3 p-2 text-center">
                                <h3 class="fw-600 fs-14 text-truncate-2 lh-1-4 mb-0 h-35px">
                                    <a href="{{ route('shop.visit', $shop->slug) }}" class="d-block text-reset">{{ $shop->name }}</a>
                                </h3>
                                <div class="rating rating-sm mt-1 text-center">
                                    {{ renderStarRating($shop->rating) }}
                                </div>
                                <div class="mt-2 text-warning fs-12 fw-700">
                                    <i class="las la-crown"></i> {{ translate('Elite Artisan') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

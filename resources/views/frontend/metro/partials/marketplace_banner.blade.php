@php
    $home_banner4_images = get_setting('home_banner4_images', null, $lang);
    $home_banner4_links = get_setting('home_banner4_links', null, $lang);
@endphp

@if ($home_banner4_images != null)
    <section class="mb-2 mb-md-3 mt-2 mt-md-3">
        <div class="container">
            <div class="aiz-carousel gutters-10 arrow-x-0 arrow-inactive-none" data-items="3" data-xl-items="3"
                data-lg-items="3" data-md-items="2" data-sm-items="1" data-xs-items="1" data-arrows="true"
                data-dots="false" data-autoplay="true">
                @foreach (json_decode($home_banner4_images, true) as $key => $value)
                    <div class="carousel-box hov-scale-img overflow-hidden">
                        <a href="{{ isset(json_decode($home_banner4_links, true)[$key]) ? json_decode($home_banner4_links, true)[$key] : '' }}"
                            class="d-block text-reset">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} Promo"
                                class="img-fluid lazyload has-transition"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@php
    $home_banner4_images = get_setting('home_banner4_images', null, $lang);
    $home_banner4_links = get_setting('home_banner4_links', null, $lang);
    $home_banner4_titles = get_setting('home_banner4_titles', null, $lang);
    $home_banner4_descriptions = get_setting('home_banner4_descriptions', null, $lang);
    $home_banner4_cta_texts = get_setting('home_banner4_cta_texts', null, $lang);
@endphp

@if ($home_banner4_images != null && get_setting('home_banner4_status', '1') == '1')
    <section class="mb-2 mb-md-3 mt-2 mt-md-3">
        <div class="container">
            <div class="aiz-carousel gutters-10 arrow-x-0 arrow-inactive-none" data-items="3" data-xl-items="3"
                data-lg-items="3" data-md-items="2" data-sm-items="1" data-xs-items="1" data-arrows="true"
                data-dots="false" data-autoplay="true">
                @foreach (json_decode($home_banner4_images, true) as $key => $value)
                    @php
                        $link = isset(json_decode($home_banner4_links, true)[$key]) ? json_decode($home_banner4_links, true)[$key] : '';
                        $title = isset(json_decode($home_banner4_titles, true)[$key]) ? json_decode($home_banner4_titles, true)[$key] : '';
                        $desc = isset(json_decode($home_banner4_descriptions, true)[$key]) ? json_decode($home_banner4_descriptions, true)[$key] : '';
                        $cta = isset(json_decode($home_banner4_cta_texts, true)[$key]) ? json_decode($home_banner4_cta_texts, true)[$key] : '';
                        $safe_title = app(\App\Services\BannerTextSanitizerService::class)->sanitize($title);
                        $safe_desc = app(\App\Services\BannerTextSanitizerService::class)->sanitize($desc);
                    @endphp
                    <div class="carousel-box hov-scale-img overflow-hidden metro-promo-banner">
                        <a href="{{ $link }}" class="d-block text-reset position-relative">
                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                data-src="{{ uploaded_asset($value) }}" alt="{{ env('APP_NAME') }} Promo"
                                class="img-fluid lazyload has-transition"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">

                            @if($safe_title || $safe_desc || $cta)
                                <div class="position-absolute w-100 h-100 top-0 left-0 d-flex flex-column justify-content-center align-items-center text-center p-4" style="background: rgba(0,0,0,0.15);">
                                    <div class="text-white metro-promo-banner-text" style="text-shadow: 0 2px 8px rgba(0,0,0,0.4);">
                                        @if($safe_title)
                                            <div class="metro-promo-banner-title fw-600 mb-2 mb-md-3">{!! $safe_title !!}</div>
                                        @endif
                                        @if($safe_desc)
                                            <div class="metro-promo-banner-description fs-16 fs-md-18 mb-4">{!! $safe_desc !!}</div>
                                        @endif
                                        @if($cta)
                                            <span class="text-white fw-500 pb-1 fs-15 fs-md-16 border-bottom border-white">{{ $cta }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

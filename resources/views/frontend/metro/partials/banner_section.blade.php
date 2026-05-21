@php 
    $lang = get_system_language()->code;
    $banner_images = get_setting($banner_key . '_images', null, $lang);
    $banner_links = get_setting($banner_key . '_links', null, $lang);
    $banner_titles = get_setting($banner_key . '_titles', null, $lang);
    $banner_descriptions = get_setting($banner_key . '_descriptions', null, $lang);
    $banner_cta_texts = get_setting($banner_key . '_cta_texts', null, $lang);
@endphp

@if ($banner_images != null)
    <div class="mb-2 mb-md-3 mt-2 mt-md-3">
        <div class="container">
            @php
                $images = json_decode($banner_images);
                $links = json_decode($banner_links, true);
                $titles = json_decode($banner_titles, true);
                $descriptions = json_decode($banner_descriptions, true);
                $cta_texts = json_decode($banner_cta_texts, true);
                $count = count($images);
                $data_md = $count >= 2 ? 2 : 1;
            @endphp
            <div class="w-100">
                <div class="aiz-carousel gutters-16 overflow-hidden arrow-inactive-none arrow-dark arrow-x-15"
                    data-items="{{ $count }}" data-xxl-items="{{ $count }}"
                    data-xl-items="{{ $count }}" data-lg-items="{{ $data_md }}"
                    data-md-items="{{ $data_md }}" data-sm-items="1" data-xs-items="1" data-arrows="true"
                    data-dots="false">
                    @foreach ($images as $key => $image)
                        @php
                            $link = $links[$key] ?? '';
                            $title = $titles[$key] ?? '';
                            $desc = $descriptions[$key] ?? '';
                            $cta = $cta_texts[$key] ?? '';
                            $safe_title = app(\App\Services\BannerTextSanitizerService::class)->sanitize($title);
                            $safe_desc = app(\App\Services\BannerTextSanitizerService::class)->sanitize($desc);
                        @endphp
                        <div class="carousel-box overflow-hidden hov-scale-img metro-promo-banner">
                            <a href="{{ $link }}" class="d-block text-reset overflow-hidden position-relative">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($image, 'medium') }}" alt="{{ env('APP_NAME') }} promo"
                                    class="img-fluid lazyload w-100 has-transition"
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
        </div>
    </div>
@endif

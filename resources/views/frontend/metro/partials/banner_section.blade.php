@php 
    $lang = get_system_language()->code;
    $banner_images = get_setting($banner_key . '_images', null, $lang);
    $banner_links = get_setting($banner_key . '_links', null, $lang);
@endphp

@if ($banner_images != null)
    <div class="mb-2 mb-md-3 mt-2 mt-md-3">
        <div class="container">
            @php
                $images = json_decode($banner_images);
                $links = json_decode($banner_links, true);
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
                        <div class="carousel-box overflow-hidden hov-scale-img">
                            <a href="{{ isset($links[$key]) ? $links[$key] : '' }}"
                                class="d-block text-reset overflow-hidden">
                                <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ uploaded_asset($image, 'medium') }}" alt="{{ env('APP_NAME') }} promo"
                                    class="img-fluid lazyload w-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

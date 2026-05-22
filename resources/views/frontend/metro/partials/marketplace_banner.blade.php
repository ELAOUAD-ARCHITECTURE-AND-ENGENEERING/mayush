@php
    $home_banner4_images = get_setting('home_banner4_images', null, $lang);
    $home_banner4_links = json_decode(get_setting('home_banner4_links', null, $lang), true) ?: [];
    $home_banner4_titles = json_decode(get_setting('home_banner4_titles', null, $lang), true) ?: [];
    $home_banner4_descriptions = json_decode(get_setting('home_banner4_descriptions', null, $lang), true) ?: [];
    $home_banner4_cta_texts = json_decode(get_setting('home_banner4_cta_texts', null, $lang), true) ?: [];
    $home_banner4_images = json_decode($home_banner4_images, true) ?: [];
@endphp

@if ($home_banner4_images !== [] && get_setting('home_banner4_status', '1') == '1')
    <section class="metro-marketplace-split mt-2 mt-md-3 mb-2 mb-md-3">
        @foreach ($home_banner4_images as $key => $value)
            @php
                $link = $home_banner4_links[$key] ?? '';
                $title = $home_banner4_titles[$key] ?? '';
                $desc = $home_banner4_descriptions[$key] ?? '';
                $cta = $home_banner4_cta_texts[$key] ?? '';
                $safe_title = app(\App\Services\BannerTextSanitizerService::class)->sanitize($title);
                $safe_desc = app(\App\Services\BannerTextSanitizerService::class)->sanitize($desc);
                $alt = trim(strip_tags($safe_title)) ?: env('APP_NAME') . ' Promo';
            @endphp
            <article class="metro-marketplace-split-item">
                <a href="{{ $link }}" class="metro-marketplace-split-link text-reset">
                    <span class="metro-marketplace-split-media" aria-hidden="true">
                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($value) }}" alt=""
                            class="lazyload has-transition"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';">
                    </span>
                    <span class="sr-only">{{ $alt }}</span>

                    @if($safe_title || $safe_desc || $cta)
                        <div class="metro-marketplace-split-content text-white">
                            @if($safe_title)
                                <div class="metro-marketplace-split-title metro-promo-banner-text">{!! $safe_title !!}</div>
                            @endif
                            @if($safe_desc)
                                <div class="metro-marketplace-split-description metro-promo-banner-text">{!! $safe_desc !!}</div>
                            @endif
                            @if($cta)
                                <span class="metro-marketplace-split-cta">{{ $cta }}</span>
                            @endif
                        </div>
                    @endif
                </a>
            </article>
        @endforeach
    </section>
@endif

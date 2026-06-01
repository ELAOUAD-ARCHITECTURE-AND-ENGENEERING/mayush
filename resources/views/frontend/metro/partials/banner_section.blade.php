@php 
    $lang = get_system_language()->code;
    $banner_images = get_setting($banner_key . '_images', null, $lang);
    $banner_links = json_decode(get_setting($banner_key . '_links', null, $lang), true) ?: [];
    $banner_titles = json_decode(get_setting($banner_key . '_titles', null, $lang), true) ?: [];
    $banner_descriptions = json_decode(get_setting($banner_key . '_descriptions', null, $lang), true) ?: [];
    $banner_cta_texts = json_decode(get_setting($banner_key . '_cta_texts', null, $lang), true) ?: [];
    $banner_collection_ids = json_decode(get_setting($banner_key . '_collection_ids', null, $lang), true) ?: [];
    $banner_images = json_decode($banner_images, true) ?: [];
@endphp

@if ($banner_images !== [] && get_setting($banner_key . '_status', '1') == '1')
    <section class="metro-marketplace-split metro-banner-level-split mt-2 mt-md-3">
        @foreach ($banner_images as $key => $image)
            @php
                $collection = !empty($banner_collection_ids[$key])
                    ? \App\Models\ProductCollection::published()->find($banner_collection_ids[$key])
                    : null;
                $link = $collection ? route('product-collections.show', $collection->slug) : ($banner_links[$key] ?? '');
                $title = $banner_titles[$key] ?? '';
                $desc = $banner_descriptions[$key] ?? '';
                $cta = $banner_cta_texts[$key] ?? '';
                $safe_title = app(\App\Services\BannerTextSanitizerService::class)->sanitize($title);
                $safe_desc = app(\App\Services\BannerTextSanitizerService::class)->sanitize($desc);
                $alt = trim(strip_tags($safe_title)) ?: env('APP_NAME') . ' Promo';
            @endphp
            <article class="metro-marketplace-split-item">
                <a href="{{ $link }}" class="metro-marketplace-split-link text-reset">
                    <span class="metro-marketplace-split-media" aria-hidden="true">
                        <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($image, 'medium') }}" alt=""
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

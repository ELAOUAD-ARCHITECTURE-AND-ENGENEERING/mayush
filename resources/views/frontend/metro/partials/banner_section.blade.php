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
                $title = trim(strip_tags(html_entity_decode(app(\App\Services\BannerTextSanitizerService::class)->sanitize((string) ($banner_titles[$key] ?? '')))));
                $desc = trim(strip_tags(html_entity_decode(app(\App\Services\BannerTextSanitizerService::class)->sanitize((string) ($banner_descriptions[$key] ?? '')))));
                $cta = $banner_cta_texts[$key] ?? '';
                $alt = $title ?: env('APP_NAME') . ' Promo';
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

                    @if($title || $desc || $cta)
                        <div class="metro-marketplace-split-content text-white">
                            @if($title)
                                <h2 class="metro-marketplace-split-title">{{ $title }}</h2>
                            @endif
                            @if($desc)
                                <p class="metro-marketplace-split-description">{{ $desc }}</p>
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

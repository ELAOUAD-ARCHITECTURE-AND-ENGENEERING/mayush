@php
    $lang = get_system_language() ? get_system_language()->code : null;
    $home_banner4_images = get_setting('home_banner4_images', null, $lang);
    $home_banner4_links = json_decode(get_setting('home_banner4_links', null, $lang), true) ?: [];
    $home_banner4_titles = json_decode(get_setting('home_banner4_titles', null, $lang), true) ?: [];
    $home_banner4_descriptions = json_decode(get_setting('home_banner4_descriptions', null, $lang), true) ?: [];
    $home_banner4_cta_texts = json_decode(get_setting('home_banner4_cta_texts', null, $lang), true) ?: [];
    $home_banner4_collection_ids = json_decode(get_setting('home_banner4_collection_ids', null, $lang), true) ?: [];
    $home_banner4_images = json_decode($home_banner4_images, true) ?: [];
@endphp

@if ($home_banner4_images !== [] && get_setting('home_banner4_status', '1') == '1')
    <section class="metro-marketplace-split mt-2 mt-md-3">
        @foreach ($home_banner4_images as $key => $value)
            @php
                $collection = !empty($home_banner4_collection_ids[$key])
                    ? app(\App\Services\StorefrontDataService::class)->productCollection((int) $home_banner4_collection_ids[$key])
                    : null;
                $link = $collection ? route('product-collections.show', $collection->slug) : ($home_banner4_links[$key] ?? '');
                $title = trim(strip_tags(html_entity_decode(app(\App\Services\BannerTextSanitizerService::class)->sanitize((string) ($home_banner4_titles[$key] ?? '')))));
                $desc = trim(strip_tags(html_entity_decode(app(\App\Services\BannerTextSanitizerService::class)->sanitize((string) ($home_banner4_descriptions[$key] ?? '')))));
                $cta = $home_banner4_cta_texts[$key] ?? '';
                $alt = $title ?: env('APP_NAME') . ' Promo';
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

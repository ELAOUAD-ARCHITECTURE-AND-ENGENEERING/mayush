@php
    $mayushSeoHomepageStats = app(\App\Services\SeoStatsService::class)->homepageStats();
@endphp

<section class="container pt-3">
    <h1 class="fs-24 fs-md-28 fw-700 text-dark mb-2">{{ translate('Furniture & Decoration Marketplace in Morocco') }}</h1>
    <p class="fs-14 fs-md-15 text-gray mb-0">
        {{ translate('Explore Mayush, Moroccan marketplace for furniture, decoration, lighting, and interior design with') }}
        {{ number_format($mayushSeoHomepageStats['published_products'] ?? 0) }} {{ translate('published products and') }}
        {{ number_format($mayushSeoHomepageStats['verified_sellers'] ?? 0) }} {{ translate('verified sellers') }}.
    </p>
</section>

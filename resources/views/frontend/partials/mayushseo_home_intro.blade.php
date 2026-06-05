@php
    $mayushSeoHomepageStats = app(\App\Services\SeoStatsService::class)->homepageStats();
@endphp

<section class="container pt-3">
    <h1 class="fs-24 fs-md-28 fw-700 text-dark mb-2">Marketplace de Mobilier & Decoration au Maroc</h1>
    <p class="fs-14 fs-md-15 text-gray mb-0">
        Explorez Mayush, marketplace marocaine de mobilier, decoration, luminaires et amenagement interieur avec
        {{ number_format($mayushSeoHomepageStats['published_products'] ?? 0) }} produits publies et
        {{ number_format($mayushSeoHomepageStats['verified_sellers'] ?? 0) }} vendeurs verifies.
    </p>
</section>

@extends('frontend.layouts.app')

@php
    $sysLang = get_system_language();
    $lang = $sysLang ? $sysLang->code : App::getLocale();
    $metaTitle = ($lang == 'ar' ? 'إلهامات الديكور وتنسيق الغرف | مايوش' : 'Toutes nos Inspirations Déco & Ambiances | Mayush');
    $metaDescription = ($lang == 'ar' ? 'اكتشف غرفاً وأفكاراً ملهمة وتسوق كل قطعة من أثاث وديكور منزلي بنقرة واحدة.' : 'Explorez des ambiances complètes pensées par nos designers et trouvez l\'inspiration pour chaque pièce de votre maison.');
@endphp

@section('meta_title'){{ $metaTitle }}@stop
@section('meta_description'){{ $metaDescription }}@stop

@section('content')
<div class="py-4 bg-light-subtle">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-transparent p-0 mb-0 fs-13">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary">{{ translate('Home') }}</a></li>
                <li class="breadcrumb-item active text-dark fw-600" aria-current="page">{{ $lang == 'ar' ? 'إلهامات الديكور' : 'Inspirations Déco' }}</li>
            </ol>
        </nav>

        <!-- Hero Header -->
        <div class="bg-dark text-white rounded-lg p-4 p-md-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #12192a 0%, #1f2a3a 100%) !important;">
            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-lg-8">
                    <span class="badge badge-warning font-weight-bold px-3 py-1 mb-2 text-dark" style="background: #D6A24E; color: #fff !important; font-size: 12px;">
                        {{ $lang == 'ar' ? 'تصميم داخلي متكامل' : 'Lookbook & Ambiances' }}
                    </span>
                    <h1 class="fs-26 fs-md-38 fw-800 text-white mb-2">
                        {{ $lang == 'ar' ? 'إلهامات الديكور وتنسيق الغرف' : 'Toutes nos Inspirations Déco' }}
                    </h1>
                    <p class="fs-14 fs-md-16 text-white-50 mb-0 max-w-600px">
                        {{ $lang == 'ar' ? 'استلهم من تصاميم الغرف المتكاملة وتسوق كل قطعة على حدة أو أعد ابتكار الديكور بالكامل في منزلك.' : 'Découvrez nos décors complets conçus pour sublimer votre intérieur. Cliquez sur chaque pièce pour explorer les détails et commander directement.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Inspirations Cards Grid -->
        <div class="row gutters-16">
            @forelse($inspirations as $inspiration)
                @php
                    $title = $inspiration->getTitle($lang);
                    $subtitle = $inspiration->getSubtitle($lang);
                    $desc = $inspiration->getDescription($lang);
                    $heroImage = $inspiration->hero_image ? Storage::disk('public')->url($inspiration->hero_image) : static_asset('assets/img/placeholder.jpg');
                @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 rounded-lg overflow-hidden shadow-sm hov-shadow-lg has-transition bg-white inspiration-listing-card">
                        <!-- Scene Image Preview -->
                        <div class="position-relative overflow-hidden" style="height: 230px; background: #12192a;">
                            <a href="{{ route('inspirations.show', $inspiration->slug) }}" class="d-block h-100">
                                <img
                                    src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                                    data-src="{{ $heroImage }}"
                                    alt="{{ $title }}"
                                    class="img-fit lazyload w-100 h-100 has-transition"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                >
                            </a>
                            <!-- Products count badge -->
                            <div class="position-absolute" style="top: 12px; left: 12px;">
                                <span class="badge badge-pill badge-dark px-2 py-1 font-weight-bold" style="background: rgba(31, 42, 58, 0.85); backdrop-filter: blur(6px); font-size: 11px;">
                                    <i class="las la-tags mr-1"></i> {{ $inspiration->items->count() }} {{ $lang == 'ar' ? 'منتجات' : 'articles' }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                            <div>
                                <h2 class="fs-18 fw-700 mb-1 text-dark">
                                    <a href="{{ route('inspirations.show', $inspiration->slug) }}" class="text-dark hov-text-primary text-decoration-none">
                                        {{ $title }}
                                    </a>
                                </h2>
                                @if($subtitle)
                                    <div class="fs-12 fw-600 text-primary mb-2">{{ $subtitle }}</div>
                                @endif
                                @if($desc)
                                    <p class="fs-13 text-secondary text-truncate-3 mb-3">{{ $desc }}</p>
                                @endif

                                <!-- Product Thumbnails Preview -->
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    @foreach($inspiration->items->take(4) as $item)
                                        @if($item->product)
                                            <a href="{{ route('product', $item->product->slug) }}" title="{{ $item->product->getTranslation('name', $lang) }}" class="size-36px rounded border overflow-hidden d-inline-block flex-shrink-0">
                                                <img src="{{ uploaded_asset($item->product->thumbnail_img) }}" alt="" class="w-100 h-100 object-fit-cover" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                            </a>
                                        @endif
                                    @endforeach
                                    @if($inspiration->items->count() > 4)
                                        <span class="size-36px rounded bg-light text-secondary fs-11 fw-700 d-inline-flex align-items-center justify-content-center border">
                                            +{{ $inspiration->items->count() - 4 }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('inspirations.show', $inspiration->slug) }}" class="btn btn-outline-primary btn-block fw-700 py-2 fs-13 rounded-pill mt-2">
                                <span>{{ $lang == 'ar' ? 'استكشف هذه الغرفة' : 'Explorer l\'ambiance' }}</span>
                                <i class="las {{ $lang == 'ar' ? 'la-arrow-left' : 'la-arrow-right' }} ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="las la-couch fs-48 text-muted mb-3"></i>
                    <h3 class="fs-18 text-dark">{{ $lang == 'ar' ? 'لا توجد إلهامات متاحة حالياً' : 'Aucune inspiration disponible pour le moment' }}</h3>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.inspiration-listing-card {
    border: 1px solid rgba(226, 224, 214, 0.7) !important;
}
.inspiration-listing-card:hover {
    transform: translateY(-4px);
    border-color: rgba(214, 162, 78, 0.4) !important;
}
.inspiration-listing-card img.img-fit {
    transition: transform 0.5s ease;
}
.inspiration-listing-card:hover img.img-fit {
    transform: scale(1.05);
}
</style>
@endsection

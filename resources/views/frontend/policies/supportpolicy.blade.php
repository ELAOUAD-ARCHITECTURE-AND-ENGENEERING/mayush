@php
    $layout = 'frontend.layouts.app';

    if (get_setting('portfolio_landing')) {
        $user = auth()->user();

        if (
            !$user ||
            ($user->user_type == 'seller'
                ? !optional($user->shop)->isFullyApproved()
                : $user->verification_status == 0)
        ) {
            $layout = 'frontend.layouts.portfolio_app';
        }
    }
@endphp

@extends($layout)

@section('meta_title'){{ $page->meta_title }}@stop

@section('meta_description'){{ $page->meta_description }}@stop

@section('meta_keywords'){{ $page->tags }}@stop
@section('meta_image'){{ uploaded_asset($page->meta_image) }}@stop
@section('canonical_url'){{ url($page->slug) }}@stop

@section('meta')
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::webPageSchema([
        'title' => \App\Services\SeoService::cleanText($page->meta_title ?: $page->getTranslation('title'), $page->getTranslation('title'), 70),
        'description' => \App\Services\SeoService::cleanText($page->meta_description ?: $page->getTranslation('content'), $page->getTranslation('title'), 170),
        'canonical' => url($page->slug),
    ])) !!}</script>
    <script type="application/ld+json">{!! \App\Services\SeoService::jsonLd(\App\Services\SeoService::breadcrumbSchema([
        ['name' => translate('Home'), 'url' => route('home')],
        ['name' => $page->getTranslation('title'), 'url' => url($page->slug)],
    ])) !!}</script>
@endsection

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ $page->getTranslation('title') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item has-transition opacity-50 hov-opacity-100">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        "{{ translate('Support Policy') }}"
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container">
        <div class="p-4 bg-white rounded shadow-sm overflow-hidden mw-100 text-left">
            @php
                echo \App\Services\SeoService::demoteH1ToH2($page->getTranslation('content'));
            @endphp
        </div>
    </div>
</section>
@endsection

@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ translate('Technical Monitoring Dashboard') }}</h1>
            </div>
        </div>
    </div>

    <div id="tech-dashboard-root">
        <div class="card">
            <div class="card-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Initializing Technical Dashboard...</p>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    @php
        $_vManifest = [];
        $_manifestPath = public_path('build/manifest.json');
        if (file_exists($_manifestPath)) {
            $_vManifest = json_decode(file_get_contents($_manifestPath), true);
        }
        $_vCss = $_vManifest['resources/sass/app.scss']['file'] ?? null;
    @endphp
    @if($_vCss)
        <link rel="stylesheet" href="{{ static_asset('build/' . $_vCss) }}">
    @endif
@endsection

@section('script_at_head')
    @php
        $_vManifest = [];
        $_manifestPath = public_path('build/manifest.json');
        if (file_exists($_manifestPath)) {
            $_vManifest = json_decode(file_get_contents($_manifestPath), true);
        }
        $_vDash = $_vManifest['resources/js/dashboard.jsx']['file'] ?? null;
    @endphp
    @if($_vDash)
        <script type="module" src="{{ static_asset('build/' . $_vDash) }}"></script>
    @endif
@endsection

@section('script')
    <script>
        window.APP_BASE_URL = "{{ rtrim(config('app.url'), '/') }}";
    </script>
@endsection

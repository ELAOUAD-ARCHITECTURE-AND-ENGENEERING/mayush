@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ translate('Technical Monitoring Dashboard') }}</h1>
            </div>
        </div>
    </div>

    <!-- Laravel Livewire technical dashboard -->
    <div id="tech-dashboard-root">
        @livewire('analytics.technical-dashboard')
    </div>
@endsection

@section('styles')
    @livewireStyles
@endsection

@section('script')
    @livewireScripts
@endsection

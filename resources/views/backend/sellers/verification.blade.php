@extends('backend.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Legacy Verification Workflow Disabled') }}</h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                {{ translate('This seller is now reviewed through the secure onboarding document workflow.') }}
            </p>
            <a href="{{ route('sellers.registration_pending', ['review_shop' => $shop->id]) }}" class="btn btn-primary">
                {{ translate('Open Onboarding Review') }}
            </a>
        </div>
    </div>
@endsection

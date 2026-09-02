@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="fw-700 mb-0">{{ translate('Recovery Codes') }}</h5>
        <p class="text-muted fs-13 mb-0">{{ translate('Save these codes in a safe place. Each code can only be used once.') }}</p>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-4">
            <i class="las la-exclamation-triangle me-1"></i>
            {{ translate('These codes will not be shown again. Please save them now.') }}
        </div>
        <div class="bg-light rounded-lg p-4 mb-4">
            <div class="row">
                @foreach($recoveryCodes as $code)
                    <div class="col-6 col-md-3 mb-2">
                        <code class="fs-14 fw-600">{{ $code }}</code>
                    </div>
                @endforeach
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary">
            {{ translate('I have saved my recovery codes') }}
        </a>
    </div>
</div>
@endsection

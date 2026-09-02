@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="fw-700 mb-0">{{ translate('Two-Factor Authentication') }}</h5>
        <p class="text-muted fs-13 mb-0">{{ translate('Add an extra layer of security to your account.') }}</p>
    </div>
    <div class="card-body">
        @if($user->hasTwoFactorEnabled())
            <div class="alert alert-success d-flex align-items-center mb-4">
                <i class="las la-shield-alt fs-24 me-2"></i>
                <span>{{ translate('Two-factor authentication is enabled.') }}</span>
            </div>
            <form action="{{ route('two-factor.disable') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-md-3 col-from-label">{{ translate('Current Password') }}</label>
                    <div class="col-md-8">
                        <input type="password" name="password" class="form-control" required
                               placeholder="{{ translate('Enter your password to disable 2FA') }}">
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-3">
                        <button type="submit" class="btn btn-sm btn-danger">
                            {{ translate('Disable Two-Factor') }}
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="row">
                <div class="col-md-5 text-center mb-4 mb-md-0">
                    <div class="border rounded-lg p-3 bg-white d-inline-block">
                        {!! $qrCodeSvg !!}
                    </div>
                    <p class="text-muted fs-12 mt-2">{{ translate('Scan this QR code with your authenticator app') }}</p>
                </div>
                <div class="col-md-7">
                    <div class="alert alert-light border mb-3">
                        <p class="fs-13 fw-600 mb-1">{{ translate('Manual Entry Key') }}</p>
                        <code class="fs-14 user-select-all">{{ $secret }}</code>
                    </div>
                    <form action="{{ route('two-factor.confirm') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="col-from-label">{{ translate('Verification Code') }}</label>
                            <input type="text" name="code" class="form-control" maxlength="6"
                                   pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
                                   placeholder="{{ translate('Enter 6-digit code') }}" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            {{ translate('Enable Two-Factor') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

@if(session('error'))
    <script>AIZ.plugins.notify('danger', '{{ session("error") }}')</script>
@endif
@if(session('success'))
    <script>AIZ.plugins.notify('success', '{{ session("success") }}')</script>
@endif
@endsection

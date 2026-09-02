@extends('auth.layouts.authentication')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 420px; margin: 60px auto;">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <i class="las la-lock fs-48 text-primary"></i>
            <h5 class="fw-700 mt-2">{{ translate('Two-Factor Verification') }}</h5>
            <p class="text-muted fs-13">{{ translate('Enter the code from your authenticator app.') }}</p>
        </div>

        <form action="{{ route('two-factor.verify') }}" method="POST" id="2fa-form">
            @csrf
            <div class="form-group" id="code-group">
                <label class="col-from-label">{{ translate('Authentication Code') }}</label>
                <input type="text" name="code" class="form-control text-center fs-20 fw-600 letter-spacing-8"
                       maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
                       placeholder="------" autofocus>
            </div>

            <div class="form-group d-none" id="recovery-group">
                <label class="col-from-label">{{ translate('Recovery Code') }}</label>
                <input type="text" name="recovery_code" class="form-control text-center"
                       placeholder="{{ translate('Enter recovery code') }}">
            </div>

            <button type="submit" class="btn btn-primary btn-block w-100 mb-3">
                {{ translate('Verify') }}
            </button>
        </form>

        <div class="text-center">
            <a href="javascript:void(0)" id="toggle-recovery" class="fs-13 text-primary">
                {{ translate('Use a recovery code instead') }}
            </a>
        </div>
    </div>
</div>

@if(session('error'))
    <script>AIZ.plugins.notify('danger', '{{ session("error") }}')</script>
@endif

<script>
    document.getElementById('toggle-recovery').addEventListener('click', function() {
        var codeGroup = document.getElementById('code-group');
        var recoveryGroup = document.getElementById('recovery-group');
        codeGroup.classList.toggle('d-none');
        recoveryGroup.classList.toggle('d-none');
        this.textContent = codeGroup.classList.contains('d-none')
            ? '{{ translate("Use authenticator code instead") }}'
            : '{{ translate("Use a recovery code instead") }}';
    });
</script>
@endsection

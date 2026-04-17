@extends('frontend.layouts.app')

@section('content')
<section class="gry-bg py-5">
    <div class="row">
        <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 mx-auto">
            <div class="card">
                <div class="text-center pt-4">
                    <h1 class="h4 fw-600">
                        {{ translate('OTP Verification') }}
                    </h1>
                    <p>{{ translate('A verification code has been sent to') }} <strong>{{ $phone }}</strong></p>
                </div>
                <div class="px-4 py-3 py-lg-4">
                    <form class="form-default" role="form" action="{{ route('validate-otp-code') }}" method="POST">
                        @csrf
                        <input type="hidden" name="phone" value="{{ $phone }}">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="{{ translate('Enter 6-digit code') }}" name="verification_code" required autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block fw-600">{{ translate('Verify & Proceed') }}</button>
                    </form>
                    <div class="mt-3 text-center">
                        <p class="text-muted">{{ translate('Didn\'t receive the code?') }}</p>
                        <a href="{{ route('resend-otp', ['phone' => $phone]) }}" class="btn btn-link p-0 text-primary">{{ translate('Resend Code') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

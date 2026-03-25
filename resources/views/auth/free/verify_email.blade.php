@extends('auth.layouts.authentication')

@section('content')
    <!-- aiz-main-wrapper -->
    <div class="aiz-main-wrapper d-flex flex-column justify-content-center bg-white">
        <section class="bg-white overflow-hidden" style="min-height:100vh;">
            <div class="row" style="min-height: 100vh;">
                <!-- Left Side Image-->
                <div class="col-xxl-6 col-lg-7">
                    <div class="h-100">
                        <img src="{{ uploaded_asset(get_setting('password_reset_page_image')) }}" alt="" class="img-fit h-100">
                    </div>
                </div>
                
                <!-- Right Side -->
                <div class="col-xxl-6 col-lg-5">
                    <div class="right-content">
                        <div class="row align-items-center justify-content-center justify-content-lg-start h-100">
                            <div class="col-xxl-6 p-4 p-lg-5">
                                <!-- Site Icon -->
                                <div class="size-48px mb-3 mx-auto mx-lg-0">
                                    <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon')}}" class="img-fit h-100">
                                </div>
                                <!-- Titles -->
                                <div class="text-center text-lg-left">
                                    <h1 class="fs-20 fs-md-24 fw-700 text-primary" style="text-transform: uppercase;">{{ translate('Verify Your Email Address') }}</h1>
                                    <h5 class="fs-14 fw-400 text-dark">{{ translate('Before proceeding, please check your email for a verification link. If you did not receive the email.') }}</h5>
                                </div>
                                
                                <!-- Reset password form -->
                                <div class="pt-3 pt-lg-4 bg-white">
                                    <div class="">
                                        <form id="resend-form" method="POST" action="{{ route('verification.resend') }}">
                                            @csrf
                                            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                                            <button type="submit" id="resend-btn" class="btn btn-primary btn-block fw-700 fs-14 rounded-0">{{ translate('Click here to request another') }}</button>
                                        </form>
                                        @if (session('resent'))
                                            <div class="alert alert-success mt-2 mb-0" role="alert">
                                                {{ translate('A fresh verification link has been sent to your email address.') }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Go Back -->
                                    <a href="{{ url()->previous() }}" class="mt-3 fs-14 fw-700 d-flex align-items-center text-primary" style="max-width: fit-content;">
                                        <i class="las la-arrow-left fs-20 mr-1"></i>
                                        {{ translate('Back to Previous Page')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('script')
@if(get_setting('google_recaptcha') == 1)
<script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cooldown and Recaptcha Logic
        const resendBtn = document.getElementById('resend-btn');
        const resendForm = document.getElementById('resend-form');
        const cooldownTime = 60; // 1 minute
        let countdown;

        function updateButton(seconds) {
            if (seconds > 0) {
                resendBtn.disabled = true;
                resendBtn.innerText = `{{ translate('Resend available in') }} ${seconds}s`;
            } else {
                resendBtn.disabled = false;
                resendBtn.innerText = `{{ translate('Click here to request another') }}`;
                clearInterval(countdown);
            }
        }

        function startCooldown() {
            const now = Math.floor(Date.now() / 1000);
            localStorage.setItem('resend_cooldown_start', now);
            runCountdown(cooldownTime);
        }

        function runCountdown(duration) {
            let remaining = duration;
            updateButton(remaining);
            countdown = setInterval(() => {
                remaining--;
                updateButton(remaining);
            }, 1000);
        }

        // Check for existing cooldown
        const savedStart = localStorage.getItem('resend_cooldown_start');
        if (savedStart) {
            const now = Math.floor(Date.now() / 1000);
            const passed = now - parseInt(savedStart);
            if (passed < cooldownTime) {
                runCountdown(cooldownTime - passed);
            }
        }

        resendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (resendBtn.disabled) return;
            
            @if(get_setting('google_recaptcha') == 1)
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ env('CAPTCHA_KEY') }}', {action: 'resend_email'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    startCooldown();
                    resendForm.submit();
                });
            });
            @else
                startCooldown();
                resendForm.submit();
            @endif
        });
    });
</script>
@endsection

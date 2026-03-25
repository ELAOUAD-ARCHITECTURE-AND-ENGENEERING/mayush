@extends('auth.layouts.authentication')

@section('content')
<div class="aiz-main-wrapper d-flex align-items-center justify-content-center bg-premium-gradient overflow-hidden" style="min-height: 100vh;">
    <!-- Abstract background elements -->
    <div class="abstract-shape shape-1"></div>
    <div class="abstract-shape shape-2"></div>
    
    <div class="container">
        <div class="row">
            <div class="col-xxl-5 col-xl-6 col-md-8 col-sm-10 mx-auto">
                <!-- Centered Premium Card -->
                <div class="waiting-card glass-morphism p-4 p-md-5 rounded-32 shadow-premium text-center position-relative z-1">
                    
                    <!-- Site Icon -->
                    <div class="size-64px mb-5 mx-auto brand-logo-container">
                        <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon')}}" class="img-fit h-100">
                    </div>

                    <!-- Main Content -->
                    <div class="content-wrapper">
                        <h1 class="fs-28 fw-800 text-dark mb-4 animate-up" style="letter-spacing: -1px;">
                            {{ translate('Verification Link Sent') }}
                        </h1>
                        
                        <div class="mail-pulse-wrapper mb-5 d-flex justify-content-center">
                            <div class="mail-pulse large">
                                <i class="las la-envelope-open-text text-primary fs-48"></i>
                            </div>
                        </div>

                        <p class="fs-16 fw-400 text-secondary mb-5 lh-1-8 animate-up delay-1">
                            {{ translate('A fresh verification link has been delivered to your inbox. Please click the link to confirm your identity and unlock your account access.') }}
                        </p>

                        <div class="waiting-indicator animate-up delay-2">
                            <div class="d-inline-flex align-items-center mb-5 px-4 py-2 rounded-pill bg-soft-primary border border-soft-primary">
                                <div class="spinner-grow spinner-grow-sm text-primary mr-3" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <span class="fs-14 fw-700 text-primary" style="text-transform: uppercase; letter-spacing: 1px;">
                                    {{ translate('Waiting for Confirmation') }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="animate-up delay-3 mb-4">
                            <form id="resend-form" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                                <button type="submit" id="resend-btn" class="btn btn-primary btn-block btn-xl rounded-pill fw-700 shadow-sm transition-3d">
                                    {{ translate('Resend Verification Email') }}
                                </button>
                            </form>
                        </div>

                        <!-- Secondary Link -->
                        <div class="animate-up delay-4">
                            <a href="{{ route('home') }}" class="fs-14 fw-700 text-muted hover-primary transition-base">
                                <i class="las la-arrow-left mr-2"></i>
                                {{ translate('Return to Shop')}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700;800&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .bg-premium-gradient {
        background: var(--primary-gradient);
        position: relative;
    }

    .abstract-shape {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        z-index: 0;
        opacity: 0.4;
    }

    .shape-1 {
        width: 400px;
        height: 400px;
        background: var(--primary);
        top: -100px;
        right: -100px;
    }

    .shape-2 {
        width: 300px;
        height: 300px;
        background: #dbc07a;
        bottom: -50px;
        left: -50px;
    }

    .rounded-32 { border-radius: 32px; }
    
    .shadow-premium {
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
    }
    
    .glass-morphism {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .bg-soft-primary { background-color: rgba(var(--primary-rgb), 0.08); }
    
    .mail-pulse.large {
        width: 100px;
        height: 100px;
        background: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        position: relative;
    }
    
    .mail-pulse.large::before,
    .mail-pulse.large::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px solid var(--primary);
        opacity: 0;
        animation: premium-pulse 3s infinite;
    }

    .mail-pulse.large::after {
        animation-delay: 1.5s;
    }
    
    @keyframes premium-pulse {
        0% { transform: scale(1); opacity: 0.6; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    
    .btn-xl {
        padding: 1.25rem 2.5rem;
        font-size: 1.1rem;
    }
    
    .transition-3d {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .transition-3d:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    
    .transition-base { transition: all 0.3s ease; }
    .hover-primary:hover { color: var(--primary) !important; text-decoration: none; }
    
    .lh-1-8 { line-height: 1.8; }

    @media (max-width: 576px) {
        .waiting-card {
            border-radius: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border: none;
        }
        .container {
            padding-left: 0;
            padding-right: 0;
        }
        .row, .col-sm-10 {
            margin: 0;
            padding: 0;
        }
        .col-sm-10 {
            max-width: 100%;
        }
    }
</style>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
@if(get_setting('google_recaptcha') == 1)
<script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple entrance
        const tl = gsap.timeline({defaults: {ease: "power4.out", duration: 1}});

        tl.from(".waiting-card", {
            scale: 0.9,
            opacity: 0,
            y: 50
        })
        .from(".brand-logo-container", {
            scale: 0.5,
            opacity: 0,
        }, "-=0.6")
        .from(".animate-up", {
            y: 30,
            opacity: 0,
            stagger: 0.15
        }, "-=0.8");

        // Subtle floating movement
        gsap.to(".abstract-shape", {
            x: "random(-50, 50)",
            y: "random(-50, 50)",
            duration: "random(10, 20)",
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        });

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
                resendBtn.innerText = `{{ translate('Resend Verification Email') }}`;
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

        // Polling logic for automatic redirect
        const checkStatusUrl = '{{ route('verification.check') }}';
        let statusInterval;

        function checkVerification() {
            fetch(checkStatusUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.verified) {
                        clearInterval(statusInterval);
                        // Show success state before redirect for premium feel
                        gsap.to(".waiting-indicator", {
                            opacity: 0,
                            y: -20,
                            duration: 0.5,
                            onComplete: () => {
                                const indicator = document.querySelector(".waiting-indicator");
                                indicator.innerHTML = `<span class="fs-14 fw-700 text-success" style="text-transform: uppercase; letter-spacing: 1px;">
                                    <i class="las la-check-circle mr-2"></i> {{ translate('Email Verified!') }}
                                </span>`;
                                gsap.fromTo(indicator, {opacity: 0, y: 20}, {opacity: 1, y: 0, duration: 0.5});
                                
                                setTimeout(() => {
                                    window.location.href = data.redirect_url;
                                }, 1500);
                            }
                        });
                    }
                })
                .catch(error => console.error('Verification check failed:', error));
        }

        // Start polling every 3 seconds
        statusInterval = setInterval(checkVerification, 3000);
    });
</script>
@endsection

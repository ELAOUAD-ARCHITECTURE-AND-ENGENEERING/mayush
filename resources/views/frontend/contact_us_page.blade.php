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

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $page->meta_title }}">
    <meta itemprop="description" content="{{ $page->meta_description }}">
    <meta itemprop="image" content="{{ uploaded_asset($page->meta_image) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="website">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $page->meta_title }}">
    <meta name="twitter:description" content="{{ $page->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($page->meta_image) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $page->meta_title }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ URL($page->slug) }}" />
    <meta property="og:image" content="{{ uploaded_asset($page->meta_image) }}" />
    <meta property="og:description" content="{{ $page->meta_description }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
@endsection

@section('content')
<section class="pt-4 my-4">
    @php
        $lang = str_replace('_', '-', app()->getLocale());
        $content = json_decode($page->getTranslation('content', $lang));
    @endphp
    <div class="container">
        <div class="" style="background-color: {{ hex2rgba(get_setting('base_color', '#d43533'), 0.02) }}">
            <div class="row">
                <div class="col-lg-6 text-center text-lg-left">
                    <div class="p-3 p-md-4 p-xl-5">
                        <h1 class="fs-36 fw-700 mb-4">{{ $page->getTranslation('title') }}</h1>
                        <p class="fs-16 fw-400 mb-5">{{ $content->description }}</p>
                        <div class="d-flex mb-5">
                            <span class="size-48px d-flex align-items-center justify-content-center border border-gray-500 rounded-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19.201" height="24" viewBox="0 0 19.201 24">
                                    <path id="c2b0eedccc4761c59dc63e9987216605" d="M13.6,2A9.611,9.611,0,0,0,4,11.6c0,3.906,2.836,7.15,5.839,10.583.95,1.087,1.934,2.212,2.81,3.349a1.2,1.2,0,0,0,1.9,0c.876-1.138,1.86-2.262,2.81-3.349,3-3.433,5.839-6.677,5.839-10.583A9.611,9.611,0,0,0,13.6,2Zm0,13.2a3.6,3.6,0,1,1,3.6-3.6A3.6,3.6,0,0,1,13.6,15.2Z" transform="translate(-4 -2)" fill="#9d9da6"/>
                                </svg>
                            </span>
                            <span class="ml-3">
                                <span class="fs-19 fw-700">{{ translate('Address') }}</span><br>
                                <span class="fs-14 text-secondary">{!! str_replace("\n", "<br>", $content->address) !!}</span>
                            </span>
                        </div>
                        <div class="d-flex mb-5">
                            <span class="size-48px d-flex align-items-center justify-content-center border border-gray-500 rounded-content">
                                <i class="las la-2x la-phone text-gray"></i>
                            </span>
                            <span class="ml-3">
                                <span class="fs-19 fw-700">{{ translate('Phone') }}</span><br>
                                <span class="fs-14 text-secondary">{{ $content->phone }}</span>
                            </span>
                        </div>
                        <div class="d-flex">
                            <span class="size-48px d-flex align-items-center justify-content-center border border-gray-500 rounded-content">
                                <i class="las la-2x la-envelope text-gray"></i>
                            </span>
                            <span class="ml-3">
                                <span class="fs-19 fw-700">{{ translate('Email Address') }}</span><br>
                                <span class="fs-14 text-secondary">{{ $content->email }}</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-3 p-md-4 p-xl-5">
                        <div class="bg-white p-4 p-xl-2rem border rounded-3">
                            <form class="form-default" id="contact-us" role="form" action="{{ route('contact.store') }}" method="POST">
                                @csrf

                                <!-- Name -->
                                <div class="form-group">
                                    <label for="name" class="fs-14 fw-700 text-soft-dark">{{  translate('Name') }}</label>
                                    <input type="text" class="form-control rounded-0" value="{{ old('name') }}" placeholder="{{  translate('Enter Name') }}" name="name" required>
                                </div>
                                <!-- Email -->
                                <div class="form-group">
                                    <label for="email" class="fs-14 fw-700 text-soft-dark">{{  translate('Email') }}</label>
                                    <input type="email" id="contact-email" class="form-control rounded-0" value="{{ old('email') }}" placeholder="{{  translate('Enter Email') }}" name="email" autocomplete="email" inputmode="email" maxlength="255" aria-describedby="contact-email-feedback" required>
                                    <small id="contact-email-feedback" class="form-text" role="status" aria-live="polite" aria-atomic="true"></small>
                                </div>
                                <!-- Phone -->
                                <div class="form-group">
                                    <label for="phone" class="fs-14 fw-700 text-soft-dark">{{  translate('Phone no.') }}</label>
                                    <input
                                        type="tel"
                                        id="contact-phone"
                                        class="form-control rounded-0"
                                        value="{{ old('phone') }}"
                                        placeholder="{{ translate('+212 6 12 34 56 78') }}"
                                        name="phone"
                                        autocomplete="tel"
                                        inputmode="tel"
                                        maxlength="23"
                                        pattern="(?:0[5-7](?:[ .\-]?[0-9]){8}|\+212[ .\-]?[5-7](?:[ .\-]?[0-9]){8})"
                                        title="{{ translate('Enter a valid Moroccan phone number, for example +212 6 12 34 56 78.') }}"
                                        aria-describedby="contact-phone-feedback"
                                        required
                                    >
                                    <small id="contact-phone-feedback" class="form-text" role="status" aria-live="polite" aria-atomic="true"></small>
                                </div>
                                <!-- Query -->
                                <div class="form-group">
                                    <label for="query" class="fs-14 fw-700 text-soft-dark">{{  translate('Tell us about your query') }}</label>
                                    <textarea
                                        class="form-control rounded-0"
                                        placeholder="{{translate('Type here...')}}"
                                        name="content"
                                        rows="3"
                                        required
                                    ></textarea>
                                </div>

                               <!-- Recaptcha -->
                                @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_contact_form') == 1) 
                                    
                                    @if ($errors->has('g-recaptcha-response'))
                                        <span class="border invalid-feedback rounded p-2 mb-3 bg-danger text-white" role="alert" style="display: block;">
                                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                        </span>
                                    @endif
                                @endif

                                <!-- Cloudflare Turnstile -->
                                @if(get_setting('cloudflare_turnstile') == 1 && get_setting('turnstile_contact_form') == 1)
                                    <div class="cf-turnstile mb-3" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                                    <input type="hidden" name="turnstile_action" value="turnstile_contact_form">
                                    @if ($errors->has('cf-turnstile-response'))
                                        <span class="border invalid-feedback rounded p-2 mb-3 bg-danger text-white" role="alert" style="display: block;">
                                            <strong>{{ $errors->first('cf-turnstile-response') }}</strong>
                                        </span>
                                    @endif
                                @endif

                                <!-- Submit Button -->
                                <div class="mt-4">
                                    @if (env('MAIL_USERNAME') == null && env('MAIL_PASSWORD') == null)
                                        <a class="btn btn-primary fw-700 fs-14 rounded-0 w-200px"
                                            href="javascript:void(1)" onclick="showWarning()">
                                            {{  translate('Submit') }}
                                        </a>
                                    @else
                                        <button type="submit" class="btn btn-primary fw-700 fs-14 rounded-0 w-200px">{{  translate('Submit') }}</button>
                                    @endif

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
    <script type="text/javascript">
        (function () {
            const form = document.getElementById('contact-us');
            const email = document.getElementById('contact-email');
            const phone = document.getElementById('contact-phone');
            const emailFeedback = document.getElementById('contact-email-feedback');
            const phoneFeedback = document.getElementById('contact-phone-feedback');
            const moroccanPhonePattern = /^(?:0[5-7](?:[ .-]?[0-9]){8}|\+212[ .-]?[5-7](?:[ .-]?[0-9]){8})$/;

            if (!form || !email || !phone || !emailFeedback || !phoneFeedback) {
                return;
            }

            const messages = {
                emailRequired: @json(translate('Email is required.')),
                emailInvalid: @json(translate('Please enter a valid email address.')),
                emailValid: @json(translate('Email format looks valid.')),
                phoneRequired: @json(translate('Phone number is required.')),
                phoneInvalid: @json(translate('Please enter a valid Moroccan phone number.')),
                phoneValid: @json(translate('Moroccan phone format looks valid.'))
            };

            function updateFeedback(input, feedback, valid, message, showFeedback) {
                input.classList.toggle('is-valid', showFeedback && valid);
                input.classList.toggle('is-invalid', showFeedback && !valid);
                input.setAttribute('aria-invalid', String(showFeedback && !valid));
                feedback.classList.toggle('text-success', showFeedback && valid);
                feedback.classList.toggle('text-danger', showFeedback && !valid);
                feedback.textContent = showFeedback ? message : '';
            }

            function validateEmail(showFeedback) {
                email.setCustomValidity('');
                const hasValue = email.value.trim().length > 0;
                const valid = hasValue && email.checkValidity();
                const message = valid
                    ? messages.emailValid
                    : (hasValue ? messages.emailInvalid : messages.emailRequired);

                email.setCustomValidity(valid ? '' : message);
                updateFeedback(email, emailFeedback, valid, message, showFeedback || hasValue);
                return valid;
            }

            function validatePhone(showFeedback) {
                phone.setCustomValidity('');
                const hasValue = phone.value.trim().length > 0;
                const valid = hasValue && moroccanPhonePattern.test(phone.value.trim()) && phone.checkValidity();
                const message = valid
                    ? messages.phoneValid
                    : (hasValue ? messages.phoneInvalid : messages.phoneRequired);

                phone.setCustomValidity(valid ? '' : message);
                updateFeedback(phone, phoneFeedback, valid, message, showFeedback || hasValue);
                return valid;
            }

            email.addEventListener('input', function () {
                validateEmail(true);
            });

            phone.addEventListener('input', function () {
                validatePhone(true);
            });

            form.addEventListener('submit', function (event) {
                const emailIsValid = validateEmail(true);
                const phoneIsValid = validatePhone(true);

                if (!emailIsValid || !phoneIsValid) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    form.reportValidity();
                }
            });

            validateEmail(false);
            validatePhone(false);
        })();
    </script>

     @if(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_contact_form') == 1)
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_KEY') }}"></script>
        
        <script type="text/javascript">
                document.getElementById('contact-us').addEventListener('submit', function(e) {
                    e.preventDefault();
                    grecaptcha.ready(function() {
                        grecaptcha.execute(`{{ env('CAPTCHA_KEY') }}`, {action: 'contact_us'}).then(function(token) {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'hidden');
                            input.setAttribute('name', 'g-recaptcha-response');
                            input.setAttribute('value', token);
                            e.target.appendChild(input);
                            e.target.submit();
                        });
                    });
                });
        </script>
    @endif


    <script type="text/javascript">
        function showWarning(){
            AIZ.plugins.notify('warning', "{{ translate('Something went wrong.') }}");
            return false;
        }
    </script>
@endsection

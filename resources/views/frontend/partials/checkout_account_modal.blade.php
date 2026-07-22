@php
    $activeCountries = get_active_countries();
    $defaultCountry = $activeCountries->first();
    $checkoutCountryCode = '+212';
    $headerLogo = get_setting('header_logo');
    $previewCart = collect($carts ?? [])->first();
    $previewProduct = $previewCart ? get_single_product($previewCart->product_id) : null;
    $previewImage = $previewProduct
        ? uploaded_asset($previewProduct->thumbnail_img, 'medium')
        : static_asset('assets/img/placeholder.jpg');
@endphp

<div class="modal fade premium-modal checkout-auth-modal" id="checkout-account-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="checkoutAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content checkout-auth-card">
            <aside class="checkout-auth-aside" aria-label="{{ translate('Checkout reassurance') }}">
                <div class="checkout-auth-brand">
                    <img src="{{ $headerLogo ? uploaded_asset($headerLogo) : static_asset('assets/img/logo.png') }}" alt="{{ get_setting('site_name') }}" loading="lazy">
                </div>

                <div class="checkout-auth-product-card">
                    <span class="checkout-auth-kicker">{{ translate('Your selection') }}</span>
                    <img class="checkout-auth-product-image" src="{{ $previewImage }}" alt="{{ $previewProduct ? $previewProduct->name : translate('Mayush interior design selection') }}" loading="lazy">
                    @if($previewProduct)
                        <strong>{{ \Illuminate\Support\Str::limit($previewProduct->name, 48) }}</strong>
                    @else
                        <strong>{{ translate('A considered choice for your interior') }}</strong>
                    @endif
                    <span class="checkout-auth-product-note">{{ translate('Your cart stays saved while you continue.') }}</span>
                </div>

                <div class="checkout-auth-aside-copy">
                    <h3>{{ translate('Your interior starts here.') }}</h3>
                    <p>{{ translate('Create your Mayush account to keep your selection and complete delivery details securely.') }}</p>
                </div>

                <div class="checkout-auth-benefits">
                    <span><i class="las la-check-circle" aria-hidden="true"></i>{{ translate('Secure checkout') }}</span>
                    <span><i class="las la-check-circle" aria-hidden="true"></i>{{ translate('Curated Moroccan design') }}</span>
                </div>
            </aside>

            <section class="checkout-auth-main">
                <button type="button" class="checkout-auth-close" data-dismiss="modal" aria-label="{{ translate('Close checkout sign in') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>

                <div class="checkout-auth-main-header">
                    <span class="checkout-auth-eyebrow">{{ translate('Almost there') }}</span>
                    <h2 id="checkoutAccountModalLabel">
                        @if(Auth::check())
                            {{ translate('Add your delivery address') }}
                        @else
                            {{ translate('Continue your checkout') }}
                        @endif
                    </h2>
                    <p>{{ translate('Sign in or create an account to continue. Your cart will remain saved.') }}</p>
                </div>

                <div class="checkout-account-errors alert alert-danger d-none" role="alert" aria-live="assertive"></div>

                @if(!Auth::check())
                    <div class="checkout-auth-tabs" role="tablist" aria-label="{{ translate('Account access') }}">
                        <a class="checkout-auth-tab active" data-toggle="tab" href="#checkout-register-tab" role="tab" aria-selected="true">{{ translate('Create account') }}</a>
                        <a class="checkout-auth-tab" data-toggle="tab" href="#checkout-login-tab" role="tab" aria-selected="false">{{ translate('Sign in') }}</a>
                    </div>
                @endif

                <div class="tab-content checkout-auth-tab-content">
                    @if(!Auth::check())
                        <div class="tab-pane fade show active" id="checkout-register-tab" role="tabpanel">
                            <div class="checkout-auth-progress" aria-label="{{ translate('Registration steps') }}">
                                <span class="active" data-checkout-step-label="1"><b>1</b>{{ translate('Account') }}</span>
                                <span data-checkout-step-label="2"><b>2</b>{{ translate('Delivery') }}</span>
                            </div>

                            <form id="checkout-register-form" data-checkout-account-form>
                                @csrf
                                <input type="hidden" name="action" value="register">

                                <div class="checkout-auth-step-panel" data-checkout-auth-step="1">
                                    <div class="form-group">
                                        <label for="checkout-register-name">{{ translate('Full name') }}</label>
                                        <input id="checkout-register-name" type="text" class="form-control" name="name" autocomplete="name" required>
                                    </div>

                                    <div class="form-group">
                                        <label>{{ translate('Continue with') }}</label>
                                        <div class="checkout-auth-methods" role="radiogroup" aria-label="{{ translate('Registration method') }}">
                                            <label class="aiz-radio">
                                                <input type="radio" name="verification_method" value="email" checked>
                                                <span>{{ translate('Email') }}</span>
                                                <span class="aiz-rounded-check"></span>
                                            </label>
                                            <label class="aiz-radio">
                                                <input type="radio" name="verification_method" value="phone">
                                                <span>{{ translate('Phone') }}</span>
                                                <span class="aiz-rounded-check"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="checkout-email-fields">
                                        <div class="form-group">
                                            <label for="checkout-register-email">{{ translate('Email address') }}</label>
                                            <input id="checkout-register-email" type="email" class="form-control" name="email" autocomplete="email" required>
                                        </div>
                                    </div>

                                    <div class="checkout-phone-fields d-none">
                                        <div class="row gutters-10">
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="checkout-register-country-code">{{ translate('Country code') }}</label>
                                                    <input id="checkout-register-country-code" type="text" class="form-control" name="account_country_code" autocomplete="tel-country-code" value="{{ $checkoutCountryCode }}">
                                                </div>
                                            </div>
                                            <div class="col-8">
                                                <div class="form-group">
                                                    <label for="checkout-register-phone">{{ translate('Phone number') }}</label>
                                                    <input id="checkout-register-phone" type="text" class="form-control" name="account_phone" autocomplete="tel">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row gutters-10">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="checkout-register-password">{{ translate('Password') }}</label>
                                                <input id="checkout-register-password" type="password" class="form-control" name="password" autocomplete="new-password" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="checkout-register-password-confirmation">{{ translate('Confirm password') }}</label>
                                                <input id="checkout-register-password-confirmation" type="password" class="form-control" name="password_confirmation" autocomplete="new-password" required>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary btn-block checkout-auth-next" data-checkout-auth-next>
                                        {{ translate('Continue to delivery') }}
                                        <i class="las la-arrow-right ml-1" aria-hidden="true"></i>
                                    </button>
                                </div>

                                <div class="checkout-auth-step-panel d-none" data-checkout-auth-step="2">
                                    @include('frontend.partials.checkout_delivery_fields', ['activeCountries' => $activeCountries, 'defaultCountry' => $defaultCountry, 'defaultCountryCode' => $checkoutCountryCode, 'field_prefix' => 'checkout-register'])

                                    <div class="checkout-auth-step-actions">
                                        <button type="button" class="btn btn-link checkout-auth-back" data-checkout-auth-back>
                                            <i class="las la-arrow-left mr-1" aria-hidden="true"></i>{{ translate('Back') }}
                                        </button>
                                        <button type="submit" class="btn btn-primary checkout-account-submit">
                                            {{ translate('Create account and continue') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="checkout-login-tab" role="tabpanel">
                            <form id="checkout-login-form" data-checkout-account-form>
                                @csrf
                                <input type="hidden" name="action" value="login">

                                <div class="form-group">
                                    <label>{{ translate('Sign in with') }}</label>
                                    <div class="checkout-auth-methods" role="radiogroup" aria-label="{{ translate('Login method') }}">
                                        <label class="aiz-radio">
                                            <input type="radio" name="login_method" value="email" checked>
                                            <span>{{ translate('Email') }}</span>
                                            <span class="aiz-rounded-check"></span>
                                        </label>
                                        <label class="aiz-radio">
                                            <input type="radio" name="login_method" value="phone">
                                            <span>{{ translate('Phone') }}</span>
                                            <span class="aiz-rounded-check"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="checkout-login-email-fields">
                                    <div class="form-group">
                                        <label for="checkout-login-email">{{ translate('Email address') }}</label>
                                        <input id="checkout-login-email" type="email" class="form-control" name="login_email" autocomplete="email" required>
                                    </div>
                                </div>

                                <div class="checkout-login-phone-fields d-none">
                                    <div class="row gutters-10">
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label for="checkout-login-country-code">{{ translate('Country code') }}</label>
                                                <input id="checkout-login-country-code" type="text" class="form-control" name="login_country_code" autocomplete="tel-country-code" value="{{ $checkoutCountryCode }}">
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="form-group">
                                                <label for="checkout-login-phone">{{ translate('Phone number') }}</label>
                                                <input id="checkout-login-phone" type="text" class="form-control" name="login_phone" autocomplete="tel">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="checkout-login-password">{{ translate('Password') }}</label>
                                    <input id="checkout-login-password" type="password" class="form-control" name="login_password" autocomplete="current-password" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block checkout-account-submit">
                                    {{ translate('Sign in and continue') }}
                                </button>
                                <a class="checkout-auth-secondary-link" href="{{ route('user.login') }}">{{ translate('Forgot your password?') }}</a>
                            </form>
                        </div>
                    @endif

                    <div class="tab-pane fade {{ Auth::check() ? 'show active' : '' }}" id="checkout-address-tab" role="tabpanel">
                        <form id="checkout-address-form" data-checkout-account-form>
                            @csrf
                            <input type="hidden" name="action" value="address">
                            @include('frontend.partials.checkout_delivery_fields', ['activeCountries' => $activeCountries, 'defaultCountry' => $defaultCountry, 'defaultCountryCode' => $checkoutCountryCode, 'field_prefix' => 'checkout-address'])
                            <button type="submit" class="btn btn-primary btn-block checkout-account-submit">
                                {{ translate('Save address and continue') }}
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

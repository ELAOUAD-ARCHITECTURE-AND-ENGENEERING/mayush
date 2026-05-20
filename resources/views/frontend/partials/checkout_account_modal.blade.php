@php
    $activeCountries = get_active_countries();
    $defaultCountry = $activeCountries->first();
@endphp

<div class="modal fade premium-modal" id="checkout-account-modal" tabindex="-1" role="dialog" aria-labelledby="checkoutAccountModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="checkoutAccountModalLabel">
                        @if(Auth::check())
                            {{ translate('Add delivery address') }}
                        @else
                            {{ translate('Continue checkout') }}
                        @endif
                    </h5>
                    <p class="mb-0 fs-13 text-muted">{{ translate('Your checkout will stay open after this step.') }}</p>
                </div>
                @if(Auth::check() && Auth::user()->addresses()->exists())
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                @endif
            </div>
            <div class="modal-body">
                <div class="checkout-account-errors alert alert-danger d-none"></div>

                @if(!Auth::check())
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#checkout-register-tab" role="tab">{{ translate('Create account') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#checkout-login-tab" role="tab">{{ translate('Log in') }}</a>
                        </li>
                    </ul>
                @endif

                <div class="tab-content">
                    @if(!Auth::check())
                        <div class="tab-pane fade show active" id="checkout-register-tab" role="tabpanel">
                            <form id="checkout-register-form" data-checkout-account-form>
                                @csrf
                                <input type="hidden" name="action" value="register">

                                <div class="form-group">
                                    <label>{{ translate('Full name') }}</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>

                                <div class="form-group">
                                    <label>{{ translate('Register with') }}</label>
                                    <div class="d-flex flex-wrap">
                                        <label class="aiz-radio mr-4">
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
                                        <label>{{ translate('Email address') }}</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                </div>

                                <div class="checkout-phone-fields d-none">
                                    <div class="row gutters-10">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ translate('Country code') }}</label>
                                                <input type="text" class="form-control" name="account_country_code" value="{{ $defaultCountry->phonecode ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label>{{ translate('Phone number') }}</label>
                                                <input type="text" class="form-control" name="account_phone">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row gutters-10">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ translate('Password') }}</label>
                                            <input type="password" class="form-control" name="password" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ translate('Confirm password') }}</label>
                                            <input type="password" class="form-control" name="password_confirmation" required>
                                        </div>
                                    </div>
                                </div>

                                @include('frontend.partials.checkout_delivery_fields', ['activeCountries' => $activeCountries, 'defaultCountry' => $defaultCountry])

                                <button type="submit" class="btn btn-primary btn-block checkout-account-submit">
                                    {{ translate('Create account and continue') }}
                                </button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="checkout-login-tab" role="tabpanel">
                            <form id="checkout-login-form" data-checkout-account-form>
                                @csrf
                                <input type="hidden" name="action" value="login">

                                <div class="form-group">
                                    <label>{{ translate('Log in with') }}</label>
                                    <div class="d-flex flex-wrap">
                                        <label class="aiz-radio mr-4">
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
                                        <label>{{ translate('Email address') }}</label>
                                        <input type="email" class="form-control" name="login_email" required>
                                    </div>
                                </div>

                                <div class="checkout-login-phone-fields d-none">
                                    <div class="row gutters-10">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>{{ translate('Country code') }}</label>
                                                <input type="text" class="form-control" name="login_country_code" value="{{ $defaultCountry->phonecode ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label>{{ translate('Phone number') }}</label>
                                                <input type="text" class="form-control" name="login_phone">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ translate('Password') }}</label>
                                    <input type="password" class="form-control" name="login_password" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block checkout-account-submit">
                                    {{ translate('Log in and continue') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="tab-pane fade {{ Auth::check() ? 'show active' : '' }}" id="checkout-address-tab" role="tabpanel">
                        <form id="checkout-address-form" data-checkout-account-form>
                            @csrf
                            <input type="hidden" name="action" value="address">
                            @include('frontend.partials.checkout_delivery_fields', ['activeCountries' => $activeCountries, 'defaultCountry' => $defaultCountry])
                            <button type="submit" class="btn btn-primary btn-block checkout-account-submit">
                                {{ translate('Save address and continue') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

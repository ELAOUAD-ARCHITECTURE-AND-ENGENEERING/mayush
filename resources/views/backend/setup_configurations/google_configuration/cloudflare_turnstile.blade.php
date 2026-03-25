@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6">{{translate('Cloudflare Turnstile Setting')}}</h3>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('cloudflare_turnstile.update') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Cloudflare Turnstile')}}</label>
                        </div>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input value="1" name="cloudflare_turnstile" type="checkbox" @if (get_setting('cloudflare_turnstile')==1)
                                    checked
                                    @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="TURNSTILE_SITE_KEY">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Site KEY')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="TURNSTILE_SITE_KEY" value="{{  config('services.turnstile.site_key') }}" placeholder="{{ translate('Site KEY') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="TURNSTILE_SECRET_KEY">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('SECRET KEY')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="TURNSTILE_SECRET_KEY" value="{{  config('services.turnstile.secret_key') }}" placeholder="{{ translate('SECRET KEY') }}" required>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('What is Cloudflare Turnstile?') }}</h5>
            </div>
            <div class="card-body">
                <p>{{ translate('Cloudflare Turnstile is a modern, user-friendly alternative to CAPTCHAs. It protects your site from bots without requiring users to solve puzzles.') }}</p>
                <ul class="list-group">
                    <li class="list-group-item">
                        {{ translate('1. Non-intrusive security for your users.') }}
                    </li>
                    <li class="list-group-item">
                        {{ translate('2. Easy to set up and manage.') }}
                    </li>
                    <li class="list-group-item">
                        {{ translate('3. Register your site and get keys from') }}
                        <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">Cloudflare Dashboard</a>.
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-12 mx-auto">
        <div class="card">
            <div class="card-body p-0">
                <ul class="list-group mb-4">
                    <li class="list-group-item bg-light" aria-current="true">{{ translate('Turnstile Applicable Pages') }}</li>
                    <li class="list-group-item">
                       <div class="row">
                            @php
                                $settings = [
                                    'turnstile_admin_login' => 'Admin Login',
                                    'turnstile_customer_login' => 'Customer Login',
                                    'turnstile_customer_register' => 'Customer Registration',
                                    'turnstile_seller_login' => 'Seller Login',
                                    'turnstile_seller_register' => 'Seller Registration',
                                    'turnstile_seller_mail_verification' => 'Seller Mail Verification',
                                    'turnstile_forgot_password' => 'Forgot Password',
                                    'turnstile_delivery_boy_login' => 'Delivery Boy Login',
                                    'turnstile_contact_form' => 'Contact Us Form',
                                ]; 
                            @endphp

                            @foreach($settings as $key => $label)
                                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                                    <div class="p-2 border mt-1 mb-2">
                                        <label class="control-label d-flex">{{ $label }}</label>
                                        <label class="aiz-switch aiz-switch-success">
                                            <input type="checkbox"
                                               onchange="triggerConfirmation(this, '{{ $key }}', '{{ $label }}')"
                                                {{ get_setting($key) == 1 ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
    <!-- confirm Modal -->
    <div id="confirm-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered" style="max-width: 540px;">
            <div class="modal-content p-2rem">
                <div class="modal-body text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="64" viewBox="0 0 72 64">
                        <path d="M40.159,3.309a4.623,4.623,0,0,0-7.981,0L.759,58.153a4.54,4.54,0,0,0,0,4.578A4.718,4.718,0,0,0,4.75,65.02H67.587a4.476,4.476,0,0,0,3.945-2.289,4.773,4.773,0,0,0,.046-4.578Zm.6,52.555H31.582V46.708h9.173Zm0-13.734H31.582V23.818h9.173Z" fill="#ffc700" />
                    </svg>
                    <p class="mt-3 mb-3 fs-16 fw-700" id="confirmation-message"></p>
                    <div>
                        <button type="button" class="btn btn-light rounded-2 mt-2 fs-13 fw-700 w-150px" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning rounded-2 mt-2 fs-13 fw-700 w-250px" onclick="confirmSettingChange()">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script type="text/javascript">
    let pendingElement = null;
    let pendingType = null;

    function triggerConfirmation(el, type, label) {
        pendingElement = el;
        pendingType = type;
        $('#confirm-modal .modal-body p').text(`Are you sure you want to change the Turnstile setting for "${label}"?`);
        $('#confirm-modal').modal('show');
    }

    function confirmSettingChange() {
        if (pendingElement && pendingType) {
            updateSettings(pendingElement, pendingType);
        }
        $('#confirm-modal').modal('hide');
        pendingElement = null;
        pendingType = null;
    }

    $('#confirm-modal').on('hidden.bs.modal', function () {
        if (pendingElement) {
            $(pendingElement).prop('checked', !$(pendingElement).is(':checked'));
            pendingElement = null;
            pendingType = null;
        }
    });

   function updateSettings(el, type) {
        if('{{env('DEMO_MODE')}}' == 'On'){
            AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
            return;
        }
        
        var value = ($(el).is(':checked')) ? 1 : 0;
         
        $.post('{{ route('business_settings.update.activation') }}', {
            _token: '{{ csrf_token() }}',
            type: type,
            value: value
        }, function(data) {
            if (data == 1) {
                AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
            } else {
                AIZ.plugins.notify('danger', 'Something went wrong');
            }
        });
    }

    $(document).ready(function () {
        var $mainToggle = $('input[name="cloudflare_turnstile"]');
        
        function toggleTurnstileChildren(isEnabled) {
            $('input[type="checkbox"]').each(function () {
                if ($(this).attr('onchange')?.includes('triggerConfirmation')) {
                    $(this).prop('disabled', !isEnabled);
                    $(this).closest('.border').css('opacity', isEnabled ? 1 : 0.5);
                }
            });
        }

        // Initialize state
        toggleTurnstileChildren($mainToggle.is(':checked'));

        // Handle dynamic changes
        $mainToggle.on('change', function() {
            toggleTurnstileChildren($(this).is(':checked'));
        });
    });
</script>
@endsection

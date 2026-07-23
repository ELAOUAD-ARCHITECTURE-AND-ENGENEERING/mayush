    @extends('backend.layouts.app')

    @section('content')
        <div class="row">
            @foreach ($payment_methods as $payment_method)
            @if(is_object($payment_method))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <img class="mr-3" src="{{ static_asset('assets/img/cards/'.$payment_method->name.'.png') }}" height="30">
                            <h5 class="mb-0 h6">{{ ucfirst(translate($payment_method->name)) }}</h5>
                        </div>
                        <label class="aiz-switch aiz-switch-success mb-0 float-right">
                            <input type="checkbox" onchange="updatePaymentSettings(this, {{ $payment_method->id }})" @if ($payment_method->active == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="card-body">
                        @include('backend.setup_configurations.payment_method.partials.'.$payment_method->name)
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <img class="mr-3" src="{{ static_asset('assets/img/cards/cmi.png') }}" height="30">
                            <h5 class="mb-0 h6">{{ translate('CMI Configuration') }}</h5>
                        </div>
                        <label class="aiz-switch aiz-switch-success mb-0 float-right">
                            <input type="checkbox" onchange="updateSettings(this, 'cmi_payment')" @if (get_setting('cmi_payment') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="card-body">
                        <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" value="cmi">
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('CMI Merchant ID') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="CMI_MERCHANT_ID" value="{{  env('CMI_MERCHANT_ID') ?: env('CMI_CLIENT_ID') }}" placeholder="{{ translate('CMI Client/Merchant ID') }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('CMI Secret Key') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="CMI_SECRET_KEY" value="{{  env('CMI_SECRET_KEY') ?: env('CMI_STORE_KEY') }}" placeholder="{{ translate('CMI Secret/Store Key') }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('CMI Gateway URL') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="CMI_GATEWAY_URL" value="{{  env('CMI_GATEWAY_URL') }}" placeholder="{{ translate('https://testpayment.cmi.co.ma/fim/est3Dgate') }}" required>
                                </div>
                            </div>
                            
                            <!-- Optional URL Overrides -->
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('CMI OK URL (Optional)') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="CMI_OK_URL" value="{{  env('CMI_OK_URL') }}" placeholder="{{ translate('Leave empty to use default route') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('CMI Fail URL (Optional)') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="CMI_FAIL_URL" value="{{  env('CMI_FAIL_URL') }}" placeholder="{{ translate('Leave empty to use default route') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('CMI Callback URL (Optional)') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="CMI_CALLBACK_URL" value="{{  env('CMI_CALLBACK_URL') }}" placeholder="{{ translate('Leave empty to use default route') }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('CMI Store Type') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="CMI_STORE_TYPE" value="{{  env('CMI_STORE_TYPE') }}" placeholder="{{ translate('CMI Store Type') }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-from-label">{{ translate('Testing Mode') }}</label>
                                </div>
                                <div class="col-md-8">
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input value="1" name="cmi_sandbox" type="checkbox" @if (Auth::user()->business_setting && \App\Models\BusinessSetting::where('type', 'cmi_sandbox')->first() && \App\Models\BusinessSetting::where('type', 'cmi_sandbox')->first()->value == 1) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-0 text-right">
                                <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                            </div>
                            <input type="hidden" name="types[]" value="CMI_MERCHANT_ID">
                            <input type="hidden" name="types[]" value="CMI_SECRET_KEY">
                            <input type="hidden" name="types[]" value="CMI_GATEWAY_URL">
                            <input type="hidden" name="types[]" value="CMI_OK_URL">
                            <input type="hidden" name="types[]" value="CMI_FAIL_URL">
                            <input type="hidden" name="types[]" value="CMI_CALLBACK_URL">
                            <input type="hidden" name="types[]" value="CMI_STORE_TYPE">
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <img class="mr-3" src="{{ static_asset('assets/img/cards/cod.png') }}" height="30">
                            <h5 class="mb-0 h6">{{ translate('Cash Payment') }}</h5>
                        </div>
                        <label class="aiz-switch aiz-switch-success mb-0 float-right">
                            <input type="checkbox" onchange="updateSettings(this, 'cash_payment')" @if (get_setting('cash_payment') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        @php
            // $demo_mode = env('DEMO_MODE') == 'On' ? true : false;
        @endphp
    @endsection

    @section('script')
        <script type="text/javascript">
            function updatePaymentSettings(el, id) {

                if('{{env('DEMO_MODE')}}' == 'On'){
                    AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                    return;
                }

                if ($(el).is(':checked')) {
                    var value = 1;
                } else {
                    var value = 0;
                }

                $.post('{{ (Route::has('payment.activation') ? route('payment.activation') : '#') }}', {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    value: value
                }, function(data) {
                    if (data == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Payment Settings updated successfully') }}');
                    } else {
                        AIZ.plugins.notify('danger', 'Something went wrong');
                    }
                });
            }

            function updateSettings(el, type) {

                if('{{env('DEMO_MODE')}}' == 'On'){
                    AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                    return;
                }

                if ($(el).is(':checked')) {
                    var value = 1;
                } else {
                    var value = 0;
                }

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
        </script>
    @endsection

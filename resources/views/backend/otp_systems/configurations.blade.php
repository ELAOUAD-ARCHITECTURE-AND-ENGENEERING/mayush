@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Twilio Credential') }}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                    @csrf
                    <input type="hidden" name="types[]" value="TWILIO_SID">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label class="col-from-label">{{ translate('TWILIO SID') }}</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="TWILIO_SID" value="{{  env('TWILIO_SID') }}" placeholder="{{ translate('TWILIO SID') }}" required>
                        </div>
                    </div>
                    <input type="hidden" name="types[]" value="TWILIO_AUTH_TOKEN">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label class="col-from-label">{{ translate('TWILIO AUTH TOKEN') }}</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="TWILIO_AUTH_TOKEN" value="{{  env('TWILIO_AUTH_TOKEN') }}" placeholder="{{ translate('TWILIO AUTH TOKEN') }}" required>
                        </div>
                    </div>
                    <input type="hidden" name="types[]" value="VALID_TWILIO_NUMBER">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label class="col-from-label">{{ translate('VALID TWILIO NUMBER') }}</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="VALID_TWILIO_NUMBER" value="{{  env('VALID_TWILIO_NUMBER') }}" placeholder="{{ translate('VALID TWILIO NUMBER') }}" required>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Nexmo Credential') }}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('update_credentials') }}" method="POST">
                    @csrf
                    <input type="hidden" name="types[]" value="NEXMO_KEY">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label class="col-from-label">{{ translate('NEXMO KEY') }}</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="NEXMO_KEY" value="{{  env('NEXMO_KEY') }}" placeholder="{{ translate('NEXMO KEY') }}" required>
                        </div>
                    </div>
                    <input type="hidden" name="types[]" value="NEXMO_SECRET">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label class="col-from-label">{{ translate('NEXMO SECRET') }}</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="NEXMO_SECRET" value="{{  env('NEXMO_SECRET') }}" placeholder="{{ translate('NEXMO SECRET') }}" required>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach ($otp_configurations as $otp_configuration)
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate(ucfirst($otp_configuration->type).' Activation') }}</h5>
            </div>
            <div class="card-body text-center">
                <label class="aiz-switch aiz-switch-success mb-0">
                    <input type="checkbox" onchange="updateActivationSettings(this, '{{ $otp_configuration->type }}')" @if ($otp_configuration->value == 1) checked @endif>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@section('script')
    <script type="text/javascript">
        function updateActivationSettings(el, type) {
            if (el.checked) {
                var value = 1;
            } else {
                var value = 0;
            }
            $.post('{{ route('otp_configurations.update.activation') }}', {
                _token: '{{ csrf_token() }}',
                type: type,
                value: value
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }
    </script>
@endsection

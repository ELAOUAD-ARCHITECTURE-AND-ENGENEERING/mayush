@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('OpenRouter AI Settings') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('ai_config.update') }}" method="POST">
                        @csrf
                        <div class="d-flex align-items-center mb-3">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input value="1" name="ai_activation" type="checkbox" @if (get_setting('ai_activation') == 1) checked @endif>
                                <span class="slider round"></span>
                            </label>
                            <label class="ml-2 mb-0">{{ translate('Enable OpenRouter AI') }}</label>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">{{ translate('OpenRouter model') }}</label>
                            <input class="form-control" name="openrouter_model" value="{{ get_setting('openrouter_model') ?: config('services.openrouter.model', 'openrouter/free') }}" placeholder="openrouter/free">
                            <small class="form-text text-muted">{{ translate('The API key is managed server-side through OPENROUTER_API_KEY.') }}</small>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save Settings') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-gray-light">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('OpenRouter configuration') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ translate('OpenRouter provides the single server-side AI gateway used by MAYUSH.') }}</p>
                    <p class="mb-0">{{ translate('Configure OPENROUTER_API_KEY and the other OPENROUTER_* variables in the server environment. Never paste the API key into the browser or commit it to the repository.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

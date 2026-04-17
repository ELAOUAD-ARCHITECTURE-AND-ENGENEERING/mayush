@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Affiliate Configuration')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('affiliate.config.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="types[]" value="affiliate_system_activation">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Affiliate System Activation')}}</label>
                        </div>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="affiliate_system_activation" value="1" @if(get_setting('affiliate_system_activation') == 1) checked @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="types[]" value="affiliate_commission_percentage">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Affiliate Commission (%)')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="number" lang="en" min="0" step="0.01" value="{{ get_setting('affiliate_commission_percentage') }}" placeholder="{{translate('5')}}" name="affiliate_commission_percentage" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save Configuration')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

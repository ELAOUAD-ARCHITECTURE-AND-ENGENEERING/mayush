@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Send Bulk SMS') }}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('sms.send') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Mobile Numbers') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="mobile_numbers" rows="3" placeholder="{{ translate('Enter numbers separated by comma') }}"></textarea>
                            <small class="text-muted">{{ translate('e.g. +8801700000000, +8801800000000') }}</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Users') }}</label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="user_emails[]" multiple data-live-search="true" data-selected-text-format="count">
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->email }}">{{ $user->name }} - {{ $user->phone }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('SMS Content') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="content" rows="5" placeholder="{{ translate('Type your message here') }}" required></textarea>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Send SMS')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

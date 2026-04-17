@extends('frontend.layouts.app')

@section('content')
<section class="gry-bg py-5">
    <div class="row">
        <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 mx-auto">
            <div class="card">
                <div class="text-center pt-4">
                    <h1 class="h4 fw-600">
                        {{ translate('Reset Password') }}
                    </h1>
                    <p>{{ translate('Enter your new password below.') }}</p>
                </div>
                <div class="px-4 py-3 py-lg-4">
                    <form class="form-default" role="form" action="{{ route('password.update.phone') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label>{{ translate('New Password') }}</label>
                            <input type="password" class="form-control" name="password" required autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Confirm Password') }}</label>
                            <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block fw-600">{{ translate('Update Password') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

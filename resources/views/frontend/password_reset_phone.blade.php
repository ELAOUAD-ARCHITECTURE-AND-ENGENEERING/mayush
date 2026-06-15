@extends('frontend.layouts.app')

@section('content')
<section class="gry-bg py-5">
    <div class="row">
        <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 mx-auto">
            <div class="card">
                <div class="text-center pt-4">
                    <h1 class="h4 fw-600">
                        {{ translate('Forgot Password?') }}
                    </h1>
                    <p>{{ translate('Enter your phone number to receive a verification code.') }}</p>
                </div>
                <div class="px-4 py-3 py-lg-4">
                    <form class="form-default" role="form" action="{{ route('send-otp') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <input type="tel" id="phone-code" class="form-control" placeholder="" name="phone" required autocomplete="off">
                            <input type="hidden" name="country_code" value="">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block fw-600">{{ translate('Send Verification Code') }}</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="{{ route('user.login') }}" class="text-reset opacity-60">{{ translate('Back to Login') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script type="text/javascript">
    var input = document.querySelector("#phone-code");
    var iti = intlTelInput(input, {
        separateDialCode: true,
        utilsScript: "{{ static_asset('assets/js/intlTelutils.js') }}",
        onlyCountries: @php echo json_encode(\App\Models\Country::where('status', 1)->pluck('code')->toArray()) @endphp,
    });

    var country = iti.getSelectedCountryData();
    $('input[name=country_code]').val(country.dialCode);

    input.addEventListener("countrychange", function(e) {
        var country = iti.getSelectedCountryData();
        $('input[name=country_code]').val(country.dialCode);
    });
</script>
@endsection

@extends('frontend.layouts.app')

@section('content')

    <!-- Steps -->
    @include('frontend.partials.checkout.stepper', ['step' => 5, 'failed' => true])

    <!-- Payment Failure -->
    <section class="py-4">
        <div class="container text-left">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    <!-- Failure Message -->
                    <div class="text-center py-4 mb-4 bg-white border">
                        <div class="mb-3">
                            <i class="las la-times-circle la-5x text-danger"></i>
                        </div>
                        <h1 class="mb-2 fs-28 fw-500 text-danger">{{ translate('Payment Failed!')}}</h1>
                        <p class="fs-16 text-soft-dark">{{ translate('We are sorry, but your payment could not be processed at this time.') }}</p>
                        @if(Session::has('payment_error'))
                            <div class="alert alert-danger mx-4 mt-3">
                                {{ Session::get('payment_error') }}
                            </div>
                        @endif
                        <p class="fs-13 text-secondary mt-3">
                            {{ translate('If funds were deducted from your account, they will be refunded automatically by your bank within 7-10 business days.') }}
                        </p>
                        <div class="mt-4">
                            <a href="{{ $retry_url }}" class="btn btn-primary fw-600 px-4">{{ translate('Retry Payment') }}</a>
                            <a href="{{ route('home') }}" class="btn btn-outline-primary fw-600 px-4 ml-2">{{ translate('Return to Home') }}</a>
                        </div>
                    </div>

                    @if($orders->count() > 0)
                    <!-- Order Recap for Reference -->
                    <div class="bg-white p-4 border mb-4">
                        <h5 class="fw-600 mb-3 fs-16 text-soft-dark pb-2 border-bottom">{{ translate('Order Details (Reference)')}}</h5>
                        <div class="row fs-14">
                            <div class="col-md-6">
                                <p><strong>{{ translate('Order ID') }}:</strong> #{{ $orders->first()->code }}</p>
                                <p><strong>{{ translate('Total Amount') }}:</strong> {{ single_price($orders->sum('grand_total')) }}</p>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <p><strong>{{ translate('Support Email') }}:</strong> {{ get_setting('contact_email') }}</p>
                                <p><strong>{{ translate('Support Phone') }}:</strong> {{ get_setting('contact_phone') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Help & Support -->
                    <div class="card shadow-none border rounded-0">
                        <div class="card-body">
                            <h5 class="fw-700 fs-16 mb-3">{{ translate('Need Help?') }}</h5>
                            <p class="fs-14">{{ translate('Common reasons for payment failure include:') }}</p>
                            <ul class="fs-14 text-muted">
                                <li>{{ translate('Insufficient funds in your account.') }}</li>
                                <li>{{ translate('Incorrect card details (Card number, Expiry, CVV).') }}</li>
                                <li>{{ translate('Transaction declined by your bank.') }}</li>
                                <li>{{ translate('Unstable internet connection during processing.') }}</li>
                            </ul>
                            <p class="fs-14 mb-0">{{ translate('If the issue persists, please contact our support team at') }} <a href="mailto:{{ get_setting('contact_email') }}">{{ get_setting('contact_email') }}</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

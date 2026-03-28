@extends('seller.layouts.app')

@section('panel_content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        {{-- Step Indicator --}}
        <div class="d-flex align-items-center justify-content-center mb-4">
            <div class="d-flex align-items-center">
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #2ecc71; width: 30px; height: 30px; line-height: 30px;"><i class="las la-check" style="font-size:14px;"></i></span>
                <span class="fw-600 text-muted mr-3">{{translate('Benefits')}}</span>
                <i class="las la-chevron-right text-muted mr-3"></i>
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #2ecc71; width: 30px; height: 30px; line-height: 30px;"><i class="las la-check" style="font-size:14px;"></i></span>
                <span class="fw-600 text-muted mr-3">{{translate('Plans')}}</span>
                <i class="las la-chevron-right text-muted mr-3"></i>
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #f1c40f; width: 30px; height: 30px; line-height: 30px;">3</span>
                <span class="fw-700 text-dark mr-3">{{translate('Recap')}}</span>
                <i class="las la-chevron-right text-muted mr-3"></i>
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #ccc; width: 30px; height: 30px; line-height: 30px;">4</span>
                <span class="text-muted">{{translate('Payment')}}</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #1a1a2e, #0f3460);">
                <h5 class="mb-0 text-white fw-700"><i class="las la-file-invoice mr-1"></i> {{translate('Order Summary')}}</h5>
            </div>
            <div class="card-body py-4">
                {{-- Plan Details --}}
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <h6 class="fw-700 mb-1">{{ $plan_name }}</h6>
                        <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                            {{ $billing_cycle === 'yearly' ? translate('Billed Annually') : translate('Billed Monthly') }}
                        </span>
                    </div>
                    <div>
                        <i class="las la-crown fs-28" style="color: #f1c40f;"></i>
                    </div>
                </div>

                {{-- Price Breakdown --}}
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted py-2">{{translate('Subtotal')}}</td>
                            <td class="text-right py-2 fw-600">{{ single_price($subtotal) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">{{translate('Tax')}} ({{ $tax_rate }}% {{translate('VAT')}})</td>
                            <td class="text-right py-2 fw-600">{{ single_price($tax_amount) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-700 py-3 fs-16">{{translate('Total')}}</td>
                            <td class="text-right fw-700 py-3 fs-16" style="color: #1a1a2e;">{{ single_price($total) }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Billing Info --}}
                <div class="rounded p-3 mb-3" style="background: #f8f9fa;">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">{{translate('Billing Cycle')}}</small>
                            <p class="fw-600 mb-0">{{ $billing_cycle === 'yearly' ? translate('Yearly') : translate('Monthly') }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">{{translate('Next Billing Date')}}</small>
                            <p class="fw-600 mb-0">{{ $next_billing_date }}</p>
                        </div>
                    </div>
                </div>

                {{-- Security Notice --}}
                <div class="d-flex align-items-center mb-3 p-2 rounded" style="background: #e8f5e9;">
                    <i class="las la-shield-alt fs-20 mr-2" style="color: #2e7d32;"></i>
                    <small style="color: #2e7d32;">{{translate('Your payment is secured by CMI 3D Secure authentication. Your card details are never stored on our servers.')}}</small>
                </div>
            </div>

            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('seller.elite.pricing') }}" class="btn btn-link text-muted px-0">
                        <i class="las la-arrow-left mr-1"></i> {{translate('Change Plan')}}
                    </a>
                    <form action="{{ route('seller.elite.process_payment') }}" method="POST">
                        @csrf
                        <input type="hidden" name="billing_cycle" value="{{ $billing_cycle }}">
                        <button type="submit" class="btn btn-lg fw-700 px-4"
                                style="background: linear-gradient(135deg, #f1c40f, #f39c12); color: #1a1a2e; border: none; border-radius: 8px;">
                            <i class="las la-lock mr-1"></i> {{translate('Proceed to Payment')}}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

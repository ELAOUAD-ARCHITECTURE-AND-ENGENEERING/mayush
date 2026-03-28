@extends('seller.layouts.app')

@section('panel_content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm overflow-hidden">
            {{-- Failure Header --}}
            <div class="text-center py-5" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle"
                         style="width: 80px; height: 80px; background: rgba(255,255,255,0.2);">
                        <i class="las la-times-circle text-white" style="font-size: 48px;"></i>
                    </div>
                </div>
                <h3 class="text-white fw-700 mb-1">{{translate('Payment Failed')}}</h3>
                <p class="text-white-50 mb-0">{{translate('Your payment could not be processed.')}}</p>
            </div>

            {{-- Error Details --}}
            <div class="card-body py-4 px-4">
                <h6 class="fw-700 mb-3"><i class="las la-exclamation-triangle mr-1 text-danger"></i> {{translate('Failure Details')}}</h6>

                <div class="rounded p-3 mb-3" style="background: #fce4ec;">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted py-2 w-40">{{translate('Error Reason')}}</td>
                                <td class="fw-600 py-2 text-danger">
                                    {{ $payment_error }}
                                </td>
                            </tr>
                            @if($subscription)
                            <tr>
                                <td class="text-muted py-2">{{translate('Application Status')}}</td>
                                <td class="py-2">
                                    <span class="badge badge-warning px-3 py-1">
                                        <i class="las la-clock mr-1"></i> {{translate('Pending')}}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">{{translate('Plan')}}</td>
                                <td class="fw-600 py-2">
                                    {{ $subscription->billing_cycle === 'yearly' ? translate('Elite Artisan — Yearly') : translate('Elite Artisan — Monthly') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">{{translate('Amount')}}</td>
                                <td class="fw-600 py-2">{{ single_price($subscription->amount_paid) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Information Box --}}
                <div class="rounded p-3 mb-3" style="background: #fff3e0;">
                    <div class="d-flex align-items-start">
                        <i class="las la-info-circle fs-20 mr-2 mt-1" style="color: #e65100;"></i>
                        <div>
                            <p class="fw-600 mb-1" style="color: #e65100;">{{translate('Your application is still pending')}}</p>
                            <small class="text-muted">
                                {{translate('No charges were made to your card. You can retry the payment at any time. Your Elite Artisan application has been saved and will remain in pending status until payment is completed.')}}
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Common Solutions --}}
                <div class="mb-3">
                    <h6 class="fw-600 fs-13 text-muted mb-2">{{translate('Common solutions:')}}</h6>
                    <ul class="fs-13 text-muted pl-3">
                        <li class="mb-1">{{translate('Ensure your card has sufficient funds')}}</li>
                        <li class="mb-1">{{translate('Check that your card supports online transactions')}}</li>
                        <li class="mb-1">{{translate('Try a different payment card')}}</li>
                        <li class="mb-1">{{translate('Contact your bank if the issue persists')}}</li>
                    </ul>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('seller.support_ticket.index') }}" class="btn btn-link text-muted px-0">
                        <i class="las la-headset mr-1"></i> {{translate('Contact Support')}}
                    </a>
                    <a href="{{ route('seller.elite.pricing') }}" class="btn btn-lg fw-700 px-4"
                       style="background: linear-gradient(135deg, #f1c40f, #f39c12); color: #1a1a2e; border: none; border-radius: 8px;">
                        <i class="las la-redo-alt mr-1"></i> {{translate('Retry Payment')}}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

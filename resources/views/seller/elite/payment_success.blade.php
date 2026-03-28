@extends('seller.layouts.app')

@section('panel_content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm overflow-hidden">
            {{-- Success Header --}}
            <div class="text-center py-5" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle"
                         style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); animation: pulse 2s infinite;">
                        <i class="las la-check-circle text-white" style="font-size: 48px;"></i>
                    </div>
                </div>
                <h3 class="text-white fw-700 mb-1">{{translate('Payment Successful!')}}</h3>
                <p class="text-white-50 mb-0">{{translate('Your Elite Artisan subscription is now active.')}}</p>
            </div>

            {{-- Receipt Details --}}
            <div class="card-body py-4 px-4">
                <h6 class="fw-700 mb-3"><i class="las la-receipt mr-1"></i> {{translate('Payment Receipt')}}</h6>

                <table class="table table-borderless mb-4">
                    <tbody>
                        <tr>
                            <td class="text-muted py-2 w-40">{{translate('Transaction ID')}}</td>
                            <td class="fw-600 py-2">
                                <code>{{ $subscription->transaction_id ?? 'N/A' }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">{{translate('Plan')}}</td>
                            <td class="fw-600 py-2">
                                {{ $subscription->billing_cycle === 'yearly' ? translate('Elite Artisan — Yearly') : translate('Elite Artisan — Monthly') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">{{translate('Billing Cycle')}}</td>
                            <td class="fw-600 py-2">
                                <span class="badge" style="background: #e8f0fe; color: #1a73e8;">
                                    {{ ucfirst($subscription->billing_cycle) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">{{translate('Amount Paid')}}</td>
                            <td class="fw-700 py-2 fs-16" style="color: #2e7d32;">
                                {{ single_price($subscription->amount_paid) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">{{translate('Payment Method')}}</td>
                            <td class="fw-600 py-2">
                                <i class="las la-credit-card mr-1"></i> {{ strtoupper($subscription->payment_method ?? 'CMI') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">{{translate('Status')}}</td>
                            <td class="py-2">
                                <span class="badge badge-success px-3 py-1">
                                    <i class="las la-check mr-1"></i> {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">{{translate('Activated On')}}</td>
                            <td class="fw-600 py-2">{{ $subscription->updated_at->format('d M Y, H:i') }}</td>
                        </tr>
                        @if($subscription->expires_at)
                        <tr>
                            <td class="text-muted py-2">{{translate('Expires On')}}</td>
                            <td class="fw-600 py-2">{{ $subscription->expires_at->format('d M Y') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>

                {{-- Activation Timeline --}}
                <div class="rounded p-3 mb-3" style="background: #e8f5e9;">
                    <div class="d-flex align-items-start">
                        <i class="las la-info-circle fs-20 mr-2 mt-1" style="color: #2e7d32;"></i>
                        <div>
                            <p class="fw-600 mb-1" style="color: #2e7d32;">{{translate('Your Elite status is now active!')}}</p>
                            <small class="text-muted">
                                {{translate('Your shop now displays the Elite badge, your products receive premium search placement, and you have access to the full Artisan Profile editor.')}}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer CTA --}}
            <div class="card-footer bg-white py-3 text-center">
                <a href="{{ route('seller.elite.index') }}" class="btn btn-lg fw-700 px-5"
                   style="background: linear-gradient(135deg, #f1c40f, #f39c12); color: #1a1a2e; border: none; border-radius: 8px;">
                    <i class="las la-crown mr-1"></i> {{translate('Go to Elite Profile')}}
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endsection

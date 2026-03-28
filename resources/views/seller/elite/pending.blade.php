@extends('seller.layouts.app')

@section('panel_content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header text-center text-white py-4" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                <div class="mb-2">
                    <i class="las la-clock" style="font-size: 48px; opacity: 0.9;"></i>
                </div>
                <h4 class="mb-1 fw-700">{{translate('Application Pending')}}</h4>
                <p class="mb-0 text-white-50 fs-14">{{translate('Your Elite Artisan application is being processed.')}}</p>
            </div>
            <div class="card-body text-center py-4">
                <p class="text-muted mb-3">
                    {{translate('We are currently processing your payment and reviewing your application. You will be notified once it is approved.')}}
                </p>

                @if($subscription)
                <div class="rounded p-3 mb-3 text-left" style="background: #f8f9fa;">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{translate('Plan')}}</small>
                            <p class="fw-600 mb-0">{{ $subscription->billing_cycle === 'yearly' ? translate('Yearly') : translate('Monthly') }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{translate('Amount')}}</small>
                            <p class="fw-600 mb-0">{{ single_price($subscription->amount_paid) }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{translate('Status')}}</small>
                            <p class="mb-0"><span class="badge badge-warning px-2">{{translate('Pending')}}</span></p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted">{{translate('Applied On')}}</small>
                            <p class="fw-600 mb-0">{{ $subscription->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        @if($subscription->transaction_id)
                        <div class="col-md-12">
                            <small class="text-muted">{{translate('Transaction ID')}}</small>
                            <p class="fw-600 mb-0"><code>{{ $subscription->transaction_id }}</code></p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <form action="{{ route('seller.elite.cancel') }}" method="POST"
                      onsubmit="return confirm('{{ translate('Are you sure you want to cancel your Elite application?') }}')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger mt-2">
                        <i class="las la-times mr-1"></i> {{translate('Cancel Application')}}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

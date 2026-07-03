@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ translate('Affiliate Dashboard') }}</h1>
            </div>
        </div>
    </div>

    @if(!$affiliate_user)
        <div class="card shadow-sm border-0 rounded-lg overflow-hidden mb-4">
            <div class="card-body p-5 text-center bg-light">
                <div class="mb-4">
                    <i class="las la-handshake la-5x text-primary opacity-50"></i>
                </div>
                <h3>{{ translate('Join Our Affiliate Program') }}</h3>
                <p class="text-muted mb-4 fs-16">{{ translate('Partner with us and earn commissions by referring new customers to our platform. Share your love for Mayush and get rewarded.') }}</p>
                <form action="{{ route('affiliate.apply') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">{{ translate('Apply Now') }}</button>
                </form>
            </div>
        </div>
    @elseif($affiliate_user->status == 0)
        <div class="alert alert-info shadow-sm border-0 d-flex align-items-center p-4">
            <i class="las la-info-circle la-2x mr-3"></i>
            <div>
                <h5 class="alert-heading mb-1">{{ translate('Application Pending') }}</h5>
                <p class="mb-0">{{ translate('Your application to join the affiliate program is currently being reviewed by our team. We will notify you once it is approved.') }}</p>
            </div>
        </div>
    @else
        <div class="row gutters-10">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-lg overflow-hidden mb-4 bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="fs-12 text-uppercase opacity-70 fw-600">{{ translate('Total Earnings') }}</div>
                            <i class="las la-wallet la-2x opacity-50"></i>
                        </div>
                        <div class="h2 fw-700 mb-0">{{ single_price($affiliate_user->balance) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-lg mb-4">
                    <div class="card-header border-light py-3">
                        <h5 class="mb-0 h6 fw-600">{{ translate('Request Withdrawal') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('affiliate.withdraw_request.store') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <input type="number" name="amount" min="1" max="{{ $affiliate_user->balance }}" step="0.01" class="form-control" placeholder="{{ translate('Amount') }}" required>
                                @error('amount')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Submit Request') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-lg mb-4">
                    <div class="card-header border-light py-3">
                        <h5 class="mb-0 h6 fw-600">{{ translate('Your Referral Link') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group mb-3">
                            <input type="text" id="referral_link" class="form-control" readonly value="{{ route('home', ['referral_code' => $user->referral_code]) }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('referral_link')">
                                    <i class="las la-copy mr-1"></i> {{ translate('Copy') }}
                                </button>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">{{ translate('Share this link on social media or with friends to earn commissions on every successful purchase they make.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-lg mb-4">
            <div class="card-header border-light py-3">
                <h5 class="mb-0 h6 fw-600">{{ translate('Payment Information') }}</h5>
            </div>
            <div class="card-body">
                <div id="payment-information-list">
                    @include('frontend.partials.payment_information.payment_info', [
                        'payment_information_id' => $user->payment_informations()->where('set_default', true)->value('id')
                            ?: $user->payment_informations()->value('id')
                    ])
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header border-light py-3">
                <h5 class="mb-0 h6 fw-600">{{ translate('Earnings History') }}</h5>
            </div>
            <div class="card-body">
                @if($affiliate_logs->count() > 0)
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ translate('Order ID') }}</th>
                                <th>{{ translate('Referral') }}</th>
                                <th>{{ translate('Amount') }}</th>
                                <th>{{ translate('Earning Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($affiliate_logs as $key => $log)
                                <tr>
                                    <td>{{ $key + 1 + ($affiliate_logs->currentPage() - 1) * $affiliate_logs->perPage() }}</td>
                                    <td>
                                        @if($log->order)
                                            <a href="{{ route('purchase_history.details', encrypt($log->order_id)) }}" class="text-primary fw-600">{{ $log->order->code }}</a>
                                        @else
                                            <span class="text-muted">{{ translate('N/A') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->order && $log->order->user)
                                            {{ $log->order->user->name }}
                                        @else
                                            {{ translate('Guest') }}
                                        @endif
                                    </td>
                                    <td class="text-success fw-700">+{{ single_price($log->amount) }}</td>
                                    <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination mt-4">
                        {{ $affiliate_logs->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('assets/img/nothing.svg') }}" class="h-100px mb-3 opacity-50">
                        <p class="text-muted">{{ translate('No earnings recorded yet. Start sharing your referral link to earn!') }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@section('script')
    @include('frontend.partials.payment_information.payment_information_js')

    <script type="text/javascript">
        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            document.execCommand("copy");
            AIZ.plugins.notify('success', '{{ translate("Referral link copied to clipboard") }}');
        }
    </script>
@endsection

@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Elite System Settings')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('elite.settings') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Elite System Active')}}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="hidden" name="elite_system_active" value="0">
                                <input type="checkbox" name="elite_system_active" value="1" @if(get_setting('elite_system_active') == 1) checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Monthly Price')}}</label>
                        <div class="col-md-8">
                            <input type="number" step="0.01" class="form-control" name="elite_monthly_price" value="{{ get_setting('elite_monthly_price', '19.99') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Yearly Price')}}</label>
                        <div class="col-md-8">
                            <input type="number" step="0.01" class="form-control" name="elite_yearly_price" value="{{ get_setting('elite_yearly_price', '179.99') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Tax Rate (%)')}}</label>
                        <div class="col-md-8">
                            <input type="number" step="0.01" class="form-control" name="elite_tax_rate" value="{{ get_setting('elite_tax_rate', '20') }}">
                            <small class="text-muted">{{translate('Applied on top of subtotal during checkout.')}}</small>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save Settings')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header row gutters-5">
                <div class="col text-center text-md-left">
                    <h5 class="mb-md-0 h6">{{ translate('Elite Subscriptions') }}</h5>
                </div>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{translate('Shop Name')}}</th>
                            <th>{{translate('Seller')}}</th>
                            <th>{{translate('Billing Cycle')}}</th>
                            <th>{{translate('Amount Paid')}}</th>
                            <th>{{translate('Transaction ID')}}</th>
                            <th>{{translate('Status')}}</th>
                            <th>{{translate('Expires At')}}</th>
                            <th class="text-right">{{translate('Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $key => $sub)
                        <tr>
                            <td>{{ ($key+1) + ($subscriptions->currentPage() - 1)*$subscriptions->perPage() }}</td>
                            <td>{{ $sub->shop->name ?? 'N/A' }}</td>
                            <td>{{ $sub->shop->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($sub->billing_cycle) }}</td>
                            <td>{{ single_price($sub->amount_paid) }}</td>
                            <td>
                                @if($sub->transaction_id)
                                    <code>{{ $sub->transaction_id }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sub->status == 'active')
                                    <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                                @elseif($sub->status == 'pending')
                                    <span class="badge badge-inline badge-warning">{{translate('Pending')}}</span>
                                @elseif($sub->status == 'expired')
                                    <span class="badge badge-inline badge-secondary">{{translate('Expired')}}</span>
                                @else
                                    <span class="badge badge-inline badge-danger">{{translate('Rejected')}}</span>
                                @endif
                            </td>
                            <td>{{ $sub->expires_at ? $sub->expires_at->format('d-m-Y H:i') : '-' }}</td>
                            <td class="text-right">
                                @if($sub->status == 'pending')
                                    <form action="{{ route('elite.approve', $sub->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">{{translate('Approve')}}</button>
                                    </form>
                                    <form action="{{ route('elite.reject', $sub->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">{{translate('Reject')}}</button>
                                    </form>
                                @elseif($sub->status == 'active')
                                    <form action="{{ route('elite.revoke', $sub->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">{{translate('Revoke')}}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $subscriptions->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

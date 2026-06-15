@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0 h6">{{ translate('ONESSTA Shipments') }}</h5>
            <a href="{{ request('back') ?: route('onessta.index') }}" class="btn btn-sm btn-soft-secondary">
                <i class="las la-arrow-left"></i> {{ translate('Back') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <form method="GET" action="{{ route('onessta.shipments') }}">
                <div class="row gutters-10">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="{{ translate('Search by code or receiver') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">{{ translate('All Statuses') }}</option>
                            <option value="WAITING_PICKUP" {{ request('status') == 'WAITING_PICKUP' ? 'selected' : '' }}>{{ translate('Waiting Pickup') }}</option>
                            <option value="PICKED_UP" {{ request('status') == 'PICKED_UP' ? 'selected' : '' }}>{{ translate('Picked Up') }}</option>
                            <option value="SENT" {{ request('status') == 'SENT' ? 'selected' : '' }}>{{ translate('Sent') }}</option>
                            <option value="RECEIVED" {{ request('status') == 'RECEIVED' ? 'selected' : '' }}>{{ translate('Received') }}</option>
                            <option value="DISTRIBUTION" {{ request('status') == 'DISTRIBUTION' ? 'selected' : '' }}>{{ translate('Distribution') }}</option>
                            <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>{{ translate('Delivered') }}</option>
                            <option value="RETURNED" {{ request('status') == 'RETURNED' ? 'selected' : '' }}>{{ translate('Returned') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">{{ translate('Filter') }}</button>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>{{ translate('Code') }}</th>
                    <th>{{ translate('Order ID') }}</th>
                    <th>{{ translate('Receiver') }}</th>
                    <th>{{ translate('Phone') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Situation') }}</th>
                    <th>{{ translate('Price') }}</th>
                    <th>{{ translate('Last Synced') }}</th>
                    <th>{{ translate('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shipments as $s)
                <tr>
                    <td><strong>{{ $s->code }}</strong></td>
                    <td>{{ $s->order_id ?? '—' }}</td>
                    <td>{{ $s->receiver }}</td>
                    <td>{{ $s->phone }}</td>
                    <td>
                        <span class="badge badge-inline @if($s->status == 'DELIVERED') badge-success @elseif($s->status == 'RETURNED') badge-danger @else badge-warning @endif">
                            {{ $s->status ?? 'N/A' }}
                        </span>
                    </td>
                    <td>{{ $s->situation ?? '—' }}</td>
                    <td>{{ single_price($s->price) }}</td>
                    <td>{{ $s->synced_at ? $s->synced_at->diffForHumans() : 'Never' }}</td>
                    <td>
                        <button class="btn btn-sm btn-icon btn-circle btn-soft-primary" onclick="viewDetails({{ $s->id }})">
                            <i class="las la-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">{{ translate('No shipments found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="aiz-pagination">
            {{ $shipments->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

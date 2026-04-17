@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0 h6">{{ translate('ONESSTA Webhook Logs') }}</h5>
            <a href="{{ request('back') ?: route('onessta.index') }}" class="btn btn-sm btn-soft-secondary">
                <i class="las la-arrow-left"></i> {{ translate('Back') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('onessta.webhook-logs') }}" class="mb-3">
            @if(request('back'))
                <input type="hidden" name="back" value="{{ request('back') }}">
            @endif
            <div class="row gutters-10">
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>{{ translate('Failed') }}</option>
                        <option value="processed" {{ $status === 'processed' ? 'selected' : '' }}>{{ translate('Processed') }}</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ translate('All') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="range" class="form-control">
                        <option value="24h" {{ $range === '24h' ? 'selected' : '' }}>{{ translate('Last 24h') }}</option>
                        <option value="7d" {{ $range === '7d' ? 'selected' : '' }}>{{ translate('Last 7 days') }}</option>
                        <option value="30d" {{ $range === '30d' ? 'selected' : '' }}>{{ translate('Last 30 days') }}</option>
                        <option value="all" {{ $range === 'all' ? 'selected' : '' }}>{{ translate('All time') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="event_type" class="form-control">
                        <option value="">{{ translate('All Events') }}</option>
                        @foreach($eventTypes as $t)
                            <option value="{{ $t }}" {{ request('event_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="{{ translate('Search') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">{{ translate('Go') }}</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-hover aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ translate('ID') }}</th>
                        <th>{{ translate('Event') }}</th>
                        <th>{{ translate('Shipment') }}</th>
                        <th>{{ translate('Signature') }}</th>
                        <th>{{ translate('Processed') }}</th>
                        <th>{{ translate('Error') }}</th>
                        <th>{{ translate('Created') }}</th>
                        <th class="text-right">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $isFailed = !$log->processed && !empty($log->error_message);
                            $sigBadge = $log->signature_valid ? 'badge-soft-success' : 'badge-soft-danger';
                            $procBadge = $log->processed ? 'badge-soft-success' : ($isFailed ? 'badge-soft-danger' : 'badge-soft-warning');
                        @endphp
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->event_type ?: '—' }}</td>
                            <td>{{ $log->shipment?->code ?: '—' }}</td>
                            <td><span class="badge badge-inline {{ $sigBadge }}">{{ $log->signature_valid ? translate('Valid') : translate('Invalid') }}</span></td>
                            <td><span class="badge badge-inline {{ $procBadge }}">{{ $log->processed ? translate('Yes') : translate('No') }}</span></td>
                            <td class="text-truncate" style="max-width: 420px;">{{ $log->error_message ?: '—' }}</td>
                            <td>{{ $log->created_at ? $log->created_at->diffForHumans() : '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('onessta.webhook-logs.show', ['log' => $log->id, 'back' => url()->full()]) }}" class="btn btn-sm btn-soft-primary">
                                    <i class="las la-eye"></i> {{ translate('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">{{ translate('No webhook logs found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="aiz-pagination mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

@endsection


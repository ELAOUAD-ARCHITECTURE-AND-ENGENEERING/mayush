@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Point Allocation Audit Log')}}</h1>
            <p class="text-muted">{{translate('Review history of bulk allocations and revert mistakes.')}}</p>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.loyalty.points.dashboard') }}" class="btn btn-outline-secondary">
                <i class="las la-angle-left"></i> {{translate('Back to Dashboard')}}
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Action History')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>{{translate('Date')}}</th>
                    <th>{{translate('Admin')}}</th>
                    <th>{{translate('Action Type')}}</th>
                    <th>{{translate('Affected Products')}}</th>
                    <th class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->admin ? $log->admin->name : 'System' }}</td>
                    <td>
                        @if($log->action_type == 'category')
                            <span class="badge badge-inline badge-info">{{translate('Category Bulk')}}</span>
                        @elseif($log->action_type == 'multi-select')
                            <span class="badge badge-inline badge-warning">{{translate('Manual Multi-Select')}}</span>
                        @elseif($log->action_type == 'csv_import')
                            <span class="badge badge-inline badge-success">{{translate('CSV Import')}}</span>
                        @else
                            <span class="badge badge-inline badge-secondary">{{ $log->action_type }}</span>
                        @endif
                    </td>
                    <td>{{ $log->affected_products_count }} {{translate('Products')}}</td>
                    <td class="text-right">
                        <form action="{{ route('admin.loyalty.points.rollback', $log->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="{{ translate('Rollback these changes') }}" onclick="return confirm('{{translate('Are you sure you want to rollback? This will restore the exact points these products had immediately BEFORE this specific action occurred.')}}')">
                                <i class="las la-undo"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">{{translate('No logs found.')}}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="aiz-pagination mt-4">
            {{ $logs->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection

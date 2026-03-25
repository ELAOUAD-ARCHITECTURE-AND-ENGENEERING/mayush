@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header row gutters-5">
        <div class="col">
            <h5 class="mb-0 h6">{{translate('Promotions Management')}}</h5>
        </div>
        <div class="col-md-3">
            <select class="form-control form-control-sm aiz-selectpicker" id="status_filter" onchange="filter_by_status()">
                <option value="all" {{ ($status_filter ?? 'all') == 'all' ? 'selected' : '' }}>{{ translate('All Statuses') }}</option>
                <option value="awaiting_admin_review" {{ ($status_filter ?? '') == 'awaiting_admin_review' ? 'selected' : '' }}>{{ translate('Pending Review') }}</option>
                <option value="approved" {{ ($status_filter ?? '') == 'approved' ? 'selected' : '' }}>{{ translate('Approved') }}</option>
                <option value="rejected" {{ ($status_filter ?? '') == 'rejected' ? 'selected' : '' }}>{{ translate('Rejected') }}</option>
                <option value="expired" {{ ($status_filter ?? '') == 'expired' ? 'selected' : '' }}>{{ translate('Expired') }}</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg">#</th>
                    <th>{{translate('Product')}}</th>
                    <th data-breakpoints="lg">{{translate('User')}}</th>
                    <th data-breakpoints="lg">{{translate('Tier')}}</th>
                    <th data-breakpoints="lg">{{translate('Duration')}}</th>
                    <th data-breakpoints="lg">{{translate('Notes')}}</th>
                    <th data-breakpoints="lg">{{translate('Status')}}</th>
                    <th class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($promotions as $key => $promotion)
                    <tr>
                        <td>{{ ($key+1) + ($promotions->currentPage() - 1)*$promotions->perPage() }}</td>
                        <td>
                            @if($promotion->product)
                                <a href="{{ route('customer.product', $promotion->product->slug) }}" target="_blank" class="text-reset text-truncate-2">{{ $promotion->product->getTranslation('name') }}</a>
                            @else
                                {{ translate('Product Not Found') }}
                            @endif
                        </td>
                        <td>{{ $promotion->user->name ?? translate('N/A') }}</td>
                        <td>{{ ucfirst($promotion->tier) }}</td>
                        <td>
                            {{ date('d-m-Y', strtotime($promotion->start_date)) }} <br>
                            {{ date('d-m-Y', strtotime($promotion->end_date)) }}
                        </td>
                        <td>
                            <span class="text-truncate-2" title="{{ $promotion->notes }}">{{ $promotion->notes ?? '-' }}</span>
                        </td>
                        <td>
                            @if($promotion->status == 'awaiting_admin_review')
                                <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                            @elseif($promotion->status == 'approved')
                                <span class="badge badge-inline badge-success">{{ translate('Approved') }}</span>
                            @elseif($promotion->status == 'rejected')
                                <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                            @elseif($promotion->status == 'expired')
                                <span class="badge badge-inline badge-secondary">{{ translate('Expired') }}</span>
                            @else
                                <span class="badge badge-inline badge-info">{{ ucfirst($promotion->status) }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @if($promotion->status != 'approved')
                            <a href="javascript:void(0)" onclick="update_status({{ $promotion->id }}, 'approved')" class="btn btn-soft-success btn-icon btn-circle btn-sm" title="{{ translate('Approve') }}">
                                <i class="las la-check"></i>
                            </a>
                            @endif
                            @if($promotion->status != 'rejected')
                            <a href="javascript:void(0)" onclick="update_status({{ $promotion->id }}, 'rejected')" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="{{ translate('Reject') }}">
                                <i class="las la-times"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $promotions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        function update_status(id, status){
            if(confirm('{{ translate("Are you sure?") }}')){
                $.post('{{ route('promotions.update_status') }}', {_token:'{{ csrf_token() }}', id:id, status:status}, function(data){
                    if(data == 1){
                        AIZ.plugins.notify('success', '{{ translate('Status updated successfully') }}');
                        location.reload();
                    }
                    else{
                        AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                    }
                });
            }
        }

        function filter_by_status(){
            var status = document.getElementById('status_filter').value;
            window.location.href = '{{ route('promotions.index') }}?status=' + status;
        }
    </script>
@endsection

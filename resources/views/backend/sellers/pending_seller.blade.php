@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Pending Sellers') }}</h1>
        </div>
    </div>
</div>

<div class="card">
    <form id="sort_sellers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Pending Seller List') }}</h5>
            </div>
            <div class="col-md-3 ml-auto">
                <input type="text" class="form-control" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name or email or mobile number & Enter') }}">
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('Phone') }}</th>
                        <th>{{ translate('Email') }}</th>
                        <th>{{ translate('Registration Date') }}</th>
                        <th data-breakpoints="lg">{{translate('Access Approval')}}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shops as $key => $shop)
                        <tr>
                            <td>{{ ($key + 1) + ($shops->currentPage() - 1) * $shops->perPage() }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ uploaded_asset($shop->logo) }}" class="size-40px img-fit mr-2" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                    <span class="text-truncate-2">{{ $shop->name }}</span>
                                </div>
                            </td>
                            <td>{{ $shop->user->phone ?? '-' }}</td>
                            <td>{{ $shop->user->email ?? '-' }}</td>
                            <td>{{ $shop->created_at ? $shop->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input
                                        @can('approve_seller') onchange="update_approved(this)" @endcan
                                        value="{{ $shop->id }}" type="checkbox"
                                        <?php if($shop->registration_approval == 1) echo "checked";?>
                                        @cannot('approve_seller') disabled @endcan
                                    >
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                @if($shop->approval_status === 'under_review')
                                    <span class="badge badge-inline badge-warning">{{ translate('Under Review') }}</span>
                                @elseif($shop->approval_status === 'rejected')
                                    <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                                @else
                                    <span class="badge badge-inline badge-secondary">{{ translate('Pending Documents') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($shop->approval_status === 'under_review')
                                    <a href="javascript:void(0)" onclick="showReviewModal({{ $shop->id }})" class="btn btn-sm btn-primary btn-icon btn-circle" title="{{ translate('Review Application') }}">
                                        <i class="las la-file-signature"></i>
                                    </a>
                                @endif
                                @can('delete_seller')
                                    <form action="{{ route('sellers.destroy', $shop->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger btn-icon btn-circle" title="{{ translate('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $shops->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')

    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="review-modal-content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function update_approved(el){
        if ('{{ env('DEMO_MODE') }}' === 'On') {
            AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
            return;
        }
        let registration_approval = el.checked ? 1 : 0;
        let shop_id = el.value;
        let $row = $(el).closest('tr');

        $.post('{{ (Route::has('sellers.registration.approved') ? route('sellers.registration.approved') : '#') }}', {
            _token: '{{ csrf_token() }}',
            id: shop_id,
            registration_approval: registration_approval
        }, function (data) {
            if (data == 1) {
                AIZ.plugins.notify('success', '{{ translate('Pending sellers Approved successfully') }}');
                if (registration_approval === 1) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        });
    }


    function showReviewModal(shop_id) {
        $.get('{{ url('admin/sellers') }}/' + shop_id + '/documents', function(data) {
            $('#review-modal-content').html(data.html);
            $('#reviewModal').modal('show');
        });
    }

</script>
@endsection

<div class="modal-header">
    <h5 class="modal-title">{{ translate('Review Seller Application') }} - {{ $shop->name }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold">{{ translate('Seller Information') }}</h6>
            <table class="table table-bordered">
                <tr>
                    <th>{{ translate('Name') }}</th>
                    <td>{{ $shop->user->name }}</td>
                </tr>
                <tr>
                    <th>{{ translate('Email') }}</th>
                    <td>{{ $shop->user->email }}</td>
                </tr>
                <tr>
                    <th>{{ translate('Phone') }}</th>
                    <td>{{ $shop->user->phone }}</td>
                </tr>
                <tr>
                    <th>{{ translate('Submitted At') }}</th>
                    <td>{{ $shop->documents_submitted_at ? $shop->documents_submitted_at->format('d M, Y h:i A') : '-' }}</td>
                </tr>
                <tr>
                    <th>{{ translate('Resubmissions') }}</th>
                    <td>{{ $shop->resubmission_count }} / 10</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold">{{ translate('Documents') }}</h6>
            <ul class="list-group">
                @if($shop->documents->count() > 0)
                    @foreach($shop->documents as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
                            <a href="{{ route('uploaded-files.info', ['id' => $doc->file_id]) }}" target="_blank" class="badge badge-primary badge-pill">{{ translate('View') }}</a>
                        </li>
                    @endforeach
                @else
                    <li class="list-group-item text-danger">{{ translate('No documents uploaded.') }}</li>
                @endif
            </ul>
        </div>
    </div>

    <hr>
    
    <div class="row">
        <div class="col-12">
            <form action="{{ route('sellers.reject', $shop->id) }}" method="POST" id="reject-form">
                @csrf
                <div class="form-group">
                    <label class="font-weight-bold text-danger">{{ translate('Rejection Reason (Required if rejecting)') }}</label>
                    <textarea class="form-control" name="rejection_reason" rows="3" placeholder="{{ translate('Please specify what is wrong with the documents so the seller can correct it...') }}"></textarea>
                </div>
            </form>
            <form action="{{ route('sellers.approve', $shop->id) }}" method="POST" id="approve-form">
                @csrf
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
    <button type="button" class="btn btn-danger" onclick="confirmRejection()">{{ translate('Reject Application') }}</button>
    <button type="button" class="btn btn-success" onclick="document.getElementById('approve-form').submit();">{{ translate('Approve Application') }}</button>
</div>

<script>
    function confirmRejection() {
        if ($('textarea[name="rejection_reason"]').val() === '') {
            AIZ.plugins.notify('danger', '{{ translate('Rejection reason is required.') }}');
            return;
        }
        document.getElementById('reject-form').submit();
    }
</script>

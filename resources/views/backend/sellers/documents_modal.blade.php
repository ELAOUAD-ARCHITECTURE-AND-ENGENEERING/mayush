<div class="modal-header">
    <h5 class="modal-title">{{ translate('Review Seller Application') }} - {{ $shop->name }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    @php($latestDocuments = $shop->documents->unique('document_type'))
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
                    @foreach($latestDocuments as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
                                <small class="d-block text-muted">{{ translate(ucfirst($doc->status ?? 'pending')) }} · v{{ $doc->version ?? 1 }}</small>
                                @if($doc->rejection_reason)
                                    <small class="d-block text-danger">{{ $doc->rejection_reason }}</small>
                                @endif
                            </span>
                            <span>
                                <a href="{{ route('sellers.documents.download', $doc->id) }}" target="_blank" class="badge badge-primary badge-pill">{{ translate('View') }}</a>
                                <form action="{{ route('sellers.documents.review', $doc->id) }}" method="POST" class="d-inline-block ml-1">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success" type="submit">{{ translate('Approve') }}</button>
                                </form>
                                <form action="{{ route('sellers.documents.review', $doc->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="{{ translate('Rejection reason') }}" required>
                                    <button class="btn btn-sm btn-outline-danger mt-1" type="submit">{{ translate('Request Correction') }}</button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                @else
                    <li class="list-group-item text-danger">{{ translate('No documents uploaded.') }}</li>
                @endif
            </ul>

            @if($shop->documents->count() > $latestDocuments->count())
                <h6 class="font-weight-bold mt-4">{{ translate('Previous Document Versions') }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>{{ translate('Type') }}</th>
                                <th>{{ translate('Version') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Reviewed At') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shop->documents->reject(fn ($doc) => $latestDocuments->contains('id', $doc->id)) as $previous)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $previous->document_type)) }}</td>
                                    <td>v{{ $previous->version ?? 1 }}</td>
                                    <td>{{ translate(ucfirst($previous->status ?? 'pending')) }}</td>
                                    <td>{{ $previous->reviewed_at ? $previous->reviewed_at->format('d M Y H:i') : '-' }}</td>
                                    <td><a href="{{ route('sellers.documents.download', $previous->id) }}" target="_blank">{{ translate('View') }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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
                <div class="form-group">
                    <label class="font-weight-bold">{{ translate('Administrator Note') }}</label>
                    <textarea class="form-control" name="admin_note" rows="2" placeholder="{{ translate('Internal application note') }}"></textarea>
                </div>
            </form>
            <form action="{{ route('sellers.approve', $shop->id) }}" method="POST" id="approve-form">
                @csrf
                <div class="form-group">
                    <label class="font-weight-bold">{{ translate('Administrator Note') }}</label>
                    <textarea class="form-control" name="admin_note" rows="2" placeholder="{{ translate('Internal application note') }}"></textarea>
                </div>
            </form>
        </div>
    </div>

    <hr>
    <h6 class="font-weight-bold">{{ translate('Review History') }}</h6>
    @if($reviewHistory->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>{{ translate('Date') }}</th>
                        <th>{{ translate('Administrator') }}</th>
                        <th>{{ translate('Action') }}</th>
                        <th>{{ translate('Details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviewHistory as $history)
                        <tr>
                            <td>{{ $history->created_at?->format('d M Y H:i') }}</td>
                            <td>{{ $history->admin?->name ?? '-' }}</td>
                            <td>{{ translate(ucwords(str_replace('_', ' ', $history->action_type))) }}</td>
                            <td>{{ $history->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted mb-0">{{ translate('No review history recorded yet.') }}</p>
    @endif
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

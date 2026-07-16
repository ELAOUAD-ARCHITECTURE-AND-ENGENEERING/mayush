<div class="card seller-profile-documents">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Seller Onboarding Documents') }}</h5>
    </div>
    <div class="card-body">
        @php($documents = $shop->documents->sortByDesc(fn ($document) => [$document->version ?? 1, $document->id]))

        @if($documents->isEmpty())
            <p class="text-muted mb-0">{{ translate('No onboarding documents have been submitted.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Document') }}</th>
                            <th>{{ translate('Version') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Review') }}</th>
                            <th>{{ translate('File') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $document->document_type)) }}</td>
                                <td>v{{ $document->version ?? 1 }}</td>
                                <td>
                                    <span class="badge badge-inline badge-{{ $document->status === 'approved' ? 'success' : ($document->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ translate(ucfirst($document->status ?? 'pending')) }}
                                    </span>
                                    @if($document->rejection_reason)
                                        <small class="d-block text-danger">{{ $document->rejection_reason }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $document->reviewed_at ? $document->reviewed_at->format('d M Y H:i') : '-' }}
                                    @if($document->reviewer)
                                        <small class="d-block text-muted">{{ $document->reviewer->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('sellers.documents.download', $document->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                                        {{ translate('Authorized Download') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

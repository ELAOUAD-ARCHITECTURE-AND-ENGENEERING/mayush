@extends('seller.layouts.app')

@section('panel_content')
    @php
        $latestDocuments = $shop->latestOnboardingDocuments();
        $missingDocuments = $shop->missingRequiredDocumentTypes();
        $rejectedDocuments = $latestDocuments->filter(fn ($document) => $document->status === 'rejected');
    @endphp

    <div class="card border-warning shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <strong>{{ translate('Seller onboarding required') }}</strong>
            <span class="badge badge-light">{{ $shop->approvalStatusLabel() }}</span>
        </div>
        <div class="card-body">
            <p class="mb-3">
                {{ translate('Your seller account is restricted until the required documents are submitted and approved by an administrator.') }}
            </p>

            <div class="row small mb-3">
                <div class="col-md-4 mb-2">
                    <strong>{{ translate('Submitted') }}:</strong>
                    {{ $shop->documents_submitted_at?->format('d M Y H:i') ?? '-' }}
                </div>
                <div class="col-md-4 mb-2">
                    <strong>{{ translate('Last reviewed') }}:</strong>
                    {{ $shop->reviewed_at?->format('d M Y H:i') ?? '-' }}
                </div>
                <div class="col-md-4 mb-2">
                    <strong>{{ translate('Resubmissions') }}:</strong>
                    {{ (int) $shop->resubmission_count }} / 10
                </div>
            </div>

            @if($missingDocuments !== [])
                <div class="alert alert-info">
                    <strong>{{ translate('Required documents needing submission or approval:') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($missingDocuments as $documentType)
                            <li>{{ translate(ucwords(str_replace('_', ' ', $documentType))) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($rejectedDocuments->isNotEmpty())
                <div class="alert alert-danger">
                    <strong>{{ translate('Corrections requested:') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($rejectedDocuments as $document)
                            <li>
                                {{ translate(ucwords(str_replace('_', ' ', $document->document_type))) }}:
                                {{ $document->rejection_reason ?: translate('Please upload a corrected version.') }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($shop->admin_note)
                <p class="text-muted">
                    <strong>{{ translate('Administrator note:') }}</strong> {{ $shop->admin_note }}
                </p>
            @endif

            <a href="{{ route('seller.onboarding.index') }}" class="btn btn-primary">
                {{ translate('Complete Registration') }}
            </a>
        </div>
    </div>
@endsection

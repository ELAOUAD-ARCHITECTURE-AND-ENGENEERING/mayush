@extends('backend.layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0 h6">{{ translate('Webhook Log Details') }} #{{ $log->id }}</h5>
            <a href="{{ $back ?: route('onessta.webhook-logs', ['back' => route('onessta.index')]) }}" class="btn btn-sm btn-soft-secondary">
                <i class="las la-arrow-left"></i> {{ translate('Back') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row gutters-10">
            <div class="col-lg-6 mb-3">
                <div class="border rounded-1 p-3 h-100">
                    <div class="mb-2"><strong>{{ translate('Event Type') }}:</strong> {{ $log->event_type ?: '—' }}</div>
                    <div class="mb-2"><strong>{{ translate('Header Event') }}:</strong> {{ $log->header_event ?: '—' }}</div>
                    <div class="mb-2"><strong>{{ translate('Signature Valid') }}:</strong>
                        <span class="badge badge-inline {{ $log->signature_valid ? 'badge-soft-success' : 'badge-soft-danger' }}">
                            {{ $log->signature_valid ? translate('Valid') : translate('Invalid') }}
                        </span>
                    </div>
                    <div class="mb-2"><strong>{{ translate('Processed') }}:</strong>
                        <span class="badge badge-inline {{ $log->processed ? 'badge-soft-success' : 'badge-soft-danger' }}">
                            {{ $log->processed ? translate('Yes') : translate('No') }}
                        </span>
                    </div>
                    <div class="mb-2"><strong>{{ translate('Processed At') }}:</strong> {{ $log->processed_at ? $log->processed_at->toDayDateTimeString() : '—' }}</div>
                    <div class="mb-2"><strong>{{ translate('Created At') }}:</strong> {{ $log->created_at ? $log->created_at->toDayDateTimeString() : '—' }}</div>
                    <div class="mb-2"><strong>{{ translate('Shipment') }}:</strong> {{ $log->shipment?->code ?: '—' }}</div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="border rounded-1 p-3 h-100">
                    <div class="mb-2"><strong>{{ translate('Header API Key') }}:</strong> {{ $log->header_api_key ?: '—' }}</div>
                    <div class="mb-2"><strong>{{ translate('Header Signature') }}:</strong> {{ $log->header_signature ?: '—' }}</div>
                    <div class="mb-2"><strong>{{ translate('Error Message') }}:</strong></div>
                    <div class="bg-light rounded-1 p-3" style="white-space: pre-wrap;">{{ $log->error_message ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="border rounded-1 p-3">
            <div class="mb-2"><strong>{{ translate('Payload') }}:</strong></div>
            @php
                $prettyPayload = is_array($payload) ? json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string) $payload;
            @endphp
            <pre class="mb-0 bg-light rounded-1 p-3" style="max-height: 420px; overflow: auto;">{{ $prettyPayload }}</pre>
        </div>
    </div>
</div>

@endsection


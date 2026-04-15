@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ translate('Saved Payment Methods') }}</h1>
            </div>
            <div class="col-md-6 text-md-right">
                <!-- Inform users they can save cards during checkout -->
                <small class="text-muted">{{ translate('Manage your vaulted CMI cards for Express Buy.') }}</small>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Saved Cards') }}</h5>
        </div>
        <div class="card-body">
            @if(count($tokens) > 0)
                <div class="table-responsive">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ translate('Card') }}</th>
                                <th>{{ translate('Card Brand') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th class="text-right">{{ translate('Options') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tokens as $key => $token)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="las la-credit-card la-2x mr-2 text-primary"></i>
                                            <span class="font-weight-600">{{ $token->maskedLabel() }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $token->card_brand ?? 'N/A' }}</td>
                                    <td>
                                        @if($token->is_default)
                                            <span class="badge badge-inline badge-success">{{ translate('Default') }}</span>
                                        @else
                                            <span class="badge badge-inline badge-secondary">{{ translate('Saved') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if(!$token->is_default)
                                            <a href="{{ route('payment.token.default', $token->id) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('Set as Default') }}">
                                                <i class="las la-check"></i>
                                            </a>
                                        @endif
                                        <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('payment.token.remove', $token->id) }}" title="{{ translate('Remove') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <img src="{{ static_asset('assets/img/nothing.svg') }}" height="90" class="mb-3" alt="{{ translate('No data found') }}">
                    <h5 class="h6 text-muted">{{ translate('No saved payment methods found') }}</h5>
                    <p class="text-sm mt-3">{{ translate('You can save your card securely during your next CMI checkout to enable 1-Click Express Buy.') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

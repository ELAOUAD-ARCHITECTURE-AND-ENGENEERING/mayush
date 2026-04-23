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
            <!-- Skeleton Loader (UI Polish) -->
            <div id="vault-skeleton" class="py-3">
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <div class="skeleton-shimmer h-20px w-10 mx-2 rounded"></div>
                    <div class="skeleton-shimmer h-20px w-25 mx-2 rounded"></div>
                    <div class="skeleton-shimmer h-20px w-20 mx-2 rounded"></div>
                    <div class="skeleton-shimmer h-20px w-15 mx-2 rounded"></div>
                    <div class="skeleton-shimmer h-20px w-15 mx-2 rounded text-right"></div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div class="skeleton-shimmer h-40px w-100 rounded mx-1"></div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div class="skeleton-shimmer h-40px w-100 rounded mx-1"></div>
                </div>
            </div>

            <div id="vault-content" class="d-none">
            @if(count($tokens) > 0)
                <div class="table-responsive">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr class="text-gray fs-12 uppercase">
                                <th class="pl-0">#</th>
                                <th>{{ translate('Card Information') }}</th>
                                <th data-breakpoints="md">{{ translate('Brand') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th class="text-right pr-0">{{ translate('Options') }}</th>
                            </tr>
                        </thead>
                        <tbody class="fs-14">
                            @foreach ($tokens as $key => $token)
                                <tr>
                                    <td class="pl-0">{{ $key+1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $brand = strtolower($token->card_brand ?? 'card');
                                                $iconClass = 'la-credit-card';
                                                $iconColor = 'text-primary';
                                                
                                                if ($brand == 'visa') {
                                                    $iconClass = 'la-cc-visa';
                                                    $iconColor = 'text-blue';
                                                } elseif ($brand == 'mastercard') {
                                                    $iconClass = 'la-cc-mastercard';
                                                    $iconColor = 'text-orange';
                                                }
                                            @endphp
                                            <div class="size-40px d-flex align-items-center justify-content-center bg-soft-light rounded-1 mr-3">
                                                <i class="lab {{ $iconClass }} la-2x {{ $iconColor }}"></i>
                                            </div>
                                            <div>
                                                <span class="d-block text-dark fw-600">{{ $token->maskedLabel() }}</span>
                                                <span class="d-block fs-11 text-gray">{{ translate('Last used') }}: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : translate('Never') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-inline badge-soft-dark border-0">{{ $token->card_brand ?? translate('Generic') }}</span>
                                    </td>
                                    <td>
                                        @if($token->is_default)
                                            <span class="badge badge-inline badge-soft-success border-0">
                                                <i class="las la-star mr-1"></i>{{ translate('Default Method') }}
                                            </span>
                                        @else
                                            <span class="badge badge-inline badge-soft-secondary border-0 text-gray">{{ translate('Backup Method') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right pr-0">
                                        <div class="d-flex justify-content-end">
                                            @if(!$token->is_default)
                                                <form action="{{ route('payment_tokens.set_default', $token->id) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-soft-primary btn-icon btn-circle btn-sm hov-bg-primary hov-text-white mx-1" title="{{ translate('Make Preferred') }}">
                                                        <i class="las la-anchor"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm hov-bg-danger hov-text-white mx-1 confirm-delete" data-href="{{ route('payment_tokens.destroy', $token->id) }}" title="{{ translate('Remove Card') }}">
                                                <i class="las la-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <img src="{{ static_asset('assets/img/nothing.svg') }}" height="90" class="mb-3" alt="{{ translate('No data found') }}">
                    <h5 class="h6 text-muted">{{ translate('No saved cards available.') }}</h5>
                    <p class="text-sm mt-3 text-secondary">{{ translate('To enable 1-Click Express Buy, please securely save your card during your next CMI checkout.') }}</p>
                </div>
            @endif
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // UI Polish: Smooth loader transition for vault tokens
        setTimeout(function() {
            $('#vault-skeleton').fadeOut(200, function() {
                $(this).addClass('d-none');
                $('#vault-content').hide().removeClass('d-none').fadeIn(300);
            });
        }, 600);
    });
</script>
@endsection

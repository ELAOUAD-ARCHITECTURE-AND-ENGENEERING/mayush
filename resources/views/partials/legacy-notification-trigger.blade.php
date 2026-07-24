@php
    $legacyNotificationVariant = ($variant ?? 'seller') === 'admin' ? 'admin' : 'seller';
    $legacyIsAdmin = $legacyNotificationVariant === 'admin';
    $legacyPreorderVisible = !$legacyIsAdmin || addon_is_activated('preorder');
    $legacyNotificationRoute = $legacyIsAdmin ? route('admin.all-notifications') : route('seller.all-notification');
@endphp

<div class="aiz-topbar-item mr-3">
    <div class="align-items-stretch d-flex dropdown">
        <a class="dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" role="button"
           aria-haspopup="true" aria-expanded="false">
            @if ($legacyIsAdmin)
                <span class="btn btn-topbar btn-circle btn-light p-0 d-flex justify-content-center align-items-center"
                      data-toggle="tooltip" data-title="{{ translate('Notification') }}">
                    <span class="d-flex align-items-center position-relative">
                        <span class="px-2 hov-svg-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16" aria-hidden="true">
                                <path d="M5.5,16a.5.5,0,0,1,0-1h3a.5.5,0,1,1,0,1Zm-5-2a.5.5,0,0,1,0-1H2V7A5.008,5.008,0,0,1,6.5,2.025V.5a.5.5,0,1,1,1,0V2.025A5.007,5.007,0,0,1,12,7H11A4,4,0,1,0,3,7v6h8V7h1v6h1.5a.5.5,0,1,1,0,1Z" fill="#9da3ae"/>
                            </svg>
                        </span>
                        @if (auth()->user() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="badge badge-sm badge-dot badge-circle badge-danger position-absolute absolute-top-right"></span>
                        @endif
                    </span>
                </span>
            @else
                <span class="btn btn-icon p-0 d-flex justify-content-center align-items-center">
                    <span class="d-flex align-items-center position-relative">
                        <i class="las la-bell fs-24" aria-hidden="true"></i>
                        @if (auth()->user()->unreadNotifications->count() > 0)
                            <span class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                        @endif
                    </span>
                </span>
            @endif
        </a>

        <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-xl py-0">
            <div class="notifications">
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link text-dark active" data-toggle="tab" data-type="order" href="javascript:void(0);"
                           data-target="#orders-notifications" role="tab">{{ translate('Orders') }}</a>
                    </li>
                    @if ($legacyPreorderVisible)
                        <li class="nav-item">
                            <a class="nav-link text-dark" data-toggle="tab" data-type="preorder" href="javascript:void(0);"
                               data-target="#preorders-notifications" role="tab">{{ translate('Preorders') }}</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link text-dark" data-toggle="tab" data-type="seller" href="javascript:void(0);"
                           data-target="#sellers-notifications" role="tab">{{ translate($legacyIsAdmin ? 'Sellers' : 'Products') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" data-toggle="tab" data-type="payout" href="javascript:void(0);"
                           data-target="#payouts-notifications" role="tab">{{ translate('Payouts') }}</a>
                    </li>
                </ul>
                <div class="tab-content c-scrollbar-light overflow-auto" style="height: 75vh; max-height: 400px; overflow-y: auto;">
                    <div class="tab-pane active" id="orders-notifications" role="tabpanel">
                        <x-unread_notification :notifications="auth()->user()->unreadNotifications()->where('type', 'App\\Notifications\\OrderNotification')->take(20)->get()" />
                    </div>
                    @if ($legacyPreorderVisible)
                        <div class="tab-pane" id="preorders-notifications" role="tabpanel">
                            <x-unread_notification :notifications="auth()->user()->unreadNotifications()->where('type', 'App\\Notifications\\PreorderNotification')->take(20)->get()" />
                        </div>
                    @endif
                    <div class="tab-pane" id="sellers-notifications" role="tabpanel">
                        <x-unread_notification :notifications="auth()->user()->unreadNotifications()->where('type', 'like', '%shop%')->take(20)->get()" />
                    </div>
                    <div class="tab-pane" id="payouts-notifications" role="tabpanel">
                        <x-unread_notification :notifications="auth()->user()->unreadNotifications()->where('type', 'App\\Notifications\\PayoutNotification')->take(20)->get()" />
                    </div>
                </div>
            </div>

            <div class="text-center border-top">
                <a href="{{ $legacyNotificationRoute }}" class="text-reset d-block py-2">
                    {{ translate('View All Notifications') }}
                </a>
            </div>
        </div>
    </div>
</div>

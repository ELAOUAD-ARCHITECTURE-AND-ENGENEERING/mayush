@php
    $notificationVariant = in_array(($variant ?? 'storefront'), ['buyer', 'seller', 'admin', 'storefront'], true)
        ? $variant
        : 'storefront';
    $notificationCenterEnabled = (bool) config('notifications_v2.enabled');
    $notificationMenuId = 'mayush-notification-menu-' . \Illuminate\Support\Str::random(12);
    $notificationInboxUrl = match ($notificationVariant) {
        'seller' => Route::has('seller.all-notification') ? route('seller.all-notification') : '#',
        'admin' => Route::has('admin.all-notifications') ? route('admin.all-notifications') : '#',
        default => Route::has('all-notifications') ? route('all-notifications') : '#',
    };
@endphp

@auth
    @once
        <style>
            .mayush-notification-center {
                /* Keep every notification surface on the Mayush Design palette. */
                --notification-accent: var(--mayush-orange, #D97434);
                --notification-accent-hover: var(--mayush-orange-hover, #C46524);
                --notification-accent-rgb: 217, 116, 52;
                --notification-surface: var(--mayush-white, #FFFFFF);
                --notification-surface-alt: var(--mayush-beige, #F5F1E8);
                --notification-text: var(--mayush-text, #333333);
                --notification-muted: var(--mayush-text-muted, #666666);
                --notification-muted-light: var(--mayush-text-light, #999999);
                --notification-border: var(--mayush-border, #E5E0D8);
                --notification-success: var(--mayush-success, #00A86B);
                --notification-danger: var(--mayush-danger, #E53935);
                --notification-warning: var(--mayush-warning, #F3AF3D);
                --notification-info: var(--mayush-info, #1565C0);
                --notification-danger-rgb: 229, 57, 53;
                --notification-warning-rgb: 243, 175, 61;
                --notification-shadow: var(--mayush-shadow-modal, 0 16px 48px rgba(0, 0, 0, .16));
                position: relative;
                z-index: 1080;
                line-height: 1.35;
            }

            .mayush-notification-trigger,
            .mayush-notification-fallback {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                padding: 0;
                color: var(--notification-text);
                background: transparent;
                border: 1px solid transparent;
                border-radius: 50%;
                cursor: pointer;
                text-decoration: none;
                transition: background-color .18s ease, border-color .18s ease, color .18s ease;
            }

            .mayush-notification-trigger:hover,
            .mayush-notification-trigger[aria-expanded="true"],
            .mayush-notification-fallback:hover {
                color: var(--notification-accent);
                background: var(--mayush-soft-orange, rgba(217, 116, 52, .10));
                border-color: rgba(var(--notification-accent-rgb), .30);
                text-decoration: none;
            }

            .mayush-notification-trigger:focus-visible,
            .mayush-notification-fallback:focus-visible,
            .mayush-notification-dropdown a:focus-visible,
            .mayush-notification-dropdown button:focus-visible {
                outline: 3px solid rgba(var(--notification-accent-rgb), .48);
                outline-offset: 2px;
            }

            .mayush-notification-icon {
                width: 19px;
                height: 19px;
                fill: none;
                stroke: currentColor;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 1.9;
            }

            .mayush-notification-badge {
                position: absolute;
                top: -3px;
                right: -4px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 18px;
                height: 18px;
                padding: 0 4px;
                color: var(--mayush-white, #FFFFFF);
                background: var(--notification-danger);
                border: 2px solid var(--notification-surface);
                border-radius: 999px;
                font-size: 10px;
                font-weight: 800;
                line-height: 1;
            }

            .mayush-notification-dropdown {
                position: absolute;
                top: calc(100% + 9px);
                right: 0;
                width: min(392px, calc(100vw - 24px));
                overflow: hidden;
                color: var(--notification-text);
                background: var(--notification-surface);
                border: 1px solid var(--notification-border);
                border-radius: 16px;
                box-shadow: var(--notification-shadow);
            }

            [dir="rtl"] .mayush-notification-dropdown {
                right: auto;
                left: 0;
            }

            .mayush-notification-dropdown[hidden] {
                display: none !important;
            }

            .mayush-notification-dropdown__header,
            .mayush-notification-dropdown__footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 17px 20px;
            }

            .mayush-notification-dropdown__header {
                border-bottom: 1px solid var(--notification-border);
            }

            .mayush-notification-dropdown__heading {
                margin: 0;
                color: inherit;
                font-size: 18px;
                font-weight: 800;
                letter-spacing: -.01em;
            }

            .mayush-notification-read-all {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 3px 0;
                color: var(--notification-accent);
                background: transparent;
                border: 0;
                cursor: pointer;
                font-size: 13px;
                font-weight: 800;
            }

            .mayush-notification-read-all svg {
                width: 17px;
                height: 17px;
                fill: none;
                stroke: currentColor;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 2.25;
            }

            .mayush-notification-read-all:disabled {
                cursor: wait;
                opacity: .55;
            }

            .mayush-notification-dropdown__body {
                max-height: min(455px, calc(100vh - 160px));
                overflow-y: auto;
            }

            .mayush-notification-groups {
                display: grid;
                gap: 0;
            }

            .mayush-notification-group__label {
                margin: 0;
                padding: 13px 20px;
                color: var(--notification-muted);
                background: var(--notification-surface-alt);
                border-bottom: 1px solid var(--notification-border);
                font-size: 13px;
                font-weight: 800;
            }

            .mayush-notification-list {
                display: grid;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .mayush-notification-item {
                display: grid;
                grid-template-columns: 42px minmax(0, 1fr) auto;
                gap: 11px;
                width: 100%;
                padding: 15px 20px;
                color: inherit;
                text-align: left;
                background: transparent;
                border: 0;
                border-bottom: 1px solid var(--notification-border);
                cursor: pointer;
                text-decoration: none;
                transition: background-color .18s ease;
            }

            .mayush-notification-item:hover {
                color: inherit;
                background: var(--notification-surface-alt);
                text-decoration: none;
            }

            .mayush-notification-item--unread {
                background: var(--mayush-soft-orange, rgba(217, 116, 52, .10));
            }

            .mayush-notification-item__icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 42px;
                height: 42px;
                color: var(--notification-muted);
                background: var(--mayush-white, #FFFFFF);
                border: 1px solid var(--notification-border);
                border-radius: 50%;
                box-shadow: var(--mayush-shadow-card, 0 2px 8px rgba(0, 0, 0, .08));
            }

            .mayush-notification-item__icon svg {
                width: 19px;
                height: 19px;
                fill: none;
                stroke: currentColor;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 1.9;
            }

            .mayush-notification-item__icon--orders,
            .mayush-notification-item__icon--payments,
            .mayush-notification-item__icon--payouts {
                color: var(--notification-accent);
                background: var(--mayush-soft-orange, rgba(217, 116, 52, .10));
            }

            .mayush-notification-item__icon--security,
            .mayush-notification-item__icon--refunds {
                color: var(--notification-danger);
                background: rgba(var(--notification-danger-rgb), .10);
            }

            .mayush-notification-item__icon--products {
                color: var(--notification-success);
                background: rgba(0, 168, 107, .10);
            }

            .mayush-notification-item__icon--messages,
            .mayush-notification-item__icon--account,
            .mayush-notification-item__icon--seller {
                color: var(--notification-info);
                background: rgba(21, 101, 192, .10);
            }

            .mayush-notification-item__icon--marketing {
                color: var(--mayush-price, #9F4F18);
                background: rgba(var(--notification-warning-rgb), .16);
            }

            .mayush-notification-item__content,
            .mayush-notification-item__title-line,
            .mayush-notification-item__title,
            .mayush-notification-item__message,
            .mayush-notification-item__time {
                display: block;
            }

            .mayush-notification-dropdown__signals {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                margin-top: 7px;
            }

            .mayush-notification-dropdown__signal {
                align-items: center;
                background: var(--notification-surface-alt);
                border-radius: var(--mayush-radius-full, 999px);
                color: var(--notification-muted);
                display: inline-flex;
                font-size: 10px;
                font-weight: 800;
                gap: 4px;
                line-height: 1;
                padding: 4px 6px;
                white-space: nowrap;
            }

            .mayush-notification-dropdown__signal svg {
                fill: none;
                height: 11px;
                stroke: currentColor;
                stroke-linecap: round;
                stroke-linejoin: round;
                stroke-width: 2;
                width: 11px;
            }

            .mayush-notification-dropdown__signal--priority-info {
                color: var(--notification-info);
            }

            .mayush-notification-dropdown__signal--priority-important {
                background: rgba(var(--notification-warning-rgb), .16);
                color: var(--mayush-price, #9F4F18);
            }

            .mayush-notification-dropdown__signal--priority-critical {
                background: rgba(var(--notification-danger-rgb), .10);
                color: var(--notification-danger);
            }

            .mayush-notification-dropdown__signal--status-unread {
                background: var(--mayush-soft-orange, rgba(217, 116, 52, .10));
                color: var(--notification-accent);
            }

            .mayush-notification-dropdown__signal--status-read {
                color: var(--notification-success);
            }

            .mayush-notification-item__title-line {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .mayush-notification-item__indicator {
                width: 6px;
                height: 6px;
                flex: 0 0 auto;
                background: var(--notification-success);
                border-radius: 50%;
            }

            .mayush-notification-item:not(.mayush-notification-item--unread) .mayush-notification-item__indicator {
                background: var(--notification-muted-light);
            }

            .mayush-notification-item__title {
                color: inherit;
                font-size: 14px;
                font-weight: 800;
                line-height: 1.35;
            }

            .mayush-notification-item__message {
                display: -webkit-box;
                margin-top: 4px;
                color: var(--notification-muted);
                font-size: 12px;
                line-height: 1.45;
                overflow: hidden;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
            }

            .mayush-notification-item__time {
                padding-top: 2px;
                color: var(--notification-muted);
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
            }

            .mayush-notification-state {
                margin: 0;
                padding: 24px 16px;
                color: var(--notification-muted);
                font-size: 13px;
                text-align: center;
            }

            .mayush-notification-state[hidden] {
                display: none;
            }

            .mayush-notification-dropdown__footer {
                justify-content: flex-start;
                border-top: 1px solid var(--notification-border);
            }

            .mayush-notification-dropdown__footer a {
                color: var(--notification-accent);
                font-size: 15px;
                font-weight: 800;
                text-decoration: none;
            }

            .mayush-notification-dropdown__footer a:hover {
                color: var(--notification-accent-hover);
                text-decoration: underline;
            }

            /* The admin top bar is light, compact, and only 55px tall. */
            .aiz-topbar .mayush-notification-center--admin {
                display: flex;
                align-items: center;
                height: 100%;
                margin-right: 1rem;
            }

            .aiz-topbar .mayush-notification-center--admin .mayush-notification-trigger {
                width: 32px;
                height: 32px;
                color: var(--notification-muted);
                background: var(--notification-surface-alt);
                border-color: var(--notification-border);
            }

            .aiz-topbar .mayush-notification-center--admin .mayush-notification-trigger:hover,
            .aiz-topbar .mayush-notification-center--admin .mayush-notification-trigger[aria-expanded="true"] {
                color: var(--notification-accent);
                background: var(--mayush-soft-orange, rgba(217, 116, 52, .10));
                border-color: rgba(var(--notification-accent-rgb), .30);
            }

            .aiz-topbar .mayush-notification-center--admin .mayush-notification-badge {
                top: -6px;
                right: -8px;
            }

            @media (max-width: 575.98px) {
                .mayush-notification-trigger,
                .mayush-notification-fallback {
                    width: 36px;
                    height: 36px;
                }

                .mayush-notification-dropdown {
                    position: fixed;
                    top: 58px;
                    right: 12px;
                    width: calc(100vw - 24px);
                }

                [dir="rtl"] .mayush-notification-dropdown {
                    right: auto;
                    left: 12px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .mayush-notification-trigger,
                .mayush-notification-fallback,
                .mayush-notification-item {
                    transition: none;
                }
            }
        </style>
    @endonce

    @if ($notificationCenterEnabled)
        <div class="mayush-notification-center mayush-notification-center--{{ $notificationVariant }} {{ $notificationVariant === 'admin' ? 'aiz-topbar-item' : '' }}"
             data-notification-center
             data-notification-variant="{{ $notificationVariant }}">
            <button type="button"
                    class="mayush-notification-trigger"
                    data-notification-trigger
                    aria-haspopup="dialog"
                    aria-expanded="false"
                    aria-controls="{{ $notificationMenuId }}"
                    aria-label="{{ translate('Notifications') }}">
                <svg class="mayush-notification-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                    <path d="M10 21h4"></path>
                </svg>
                <span class="mayush-notification-badge" data-notification-unread-count hidden aria-hidden="true">0</span>
                <span class="sr-only" data-notification-unread-label>{{ translate('No unread notifications') }}</span>
            </button>

            <section id="{{ $notificationMenuId }}"
                     class="mayush-notification-dropdown"
                     data-notification-dropdown
                     aria-label="{{ translate('Notifications') }}"
                     hidden>
                <div class="mayush-notification-dropdown__header">
                    <h2 class="mayush-notification-dropdown__heading">{{ translate('Notifications') }}</h2>
                    <button type="button" class="mayush-notification-read-all" data-notification-read-all>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 12 4 4L20 4"></path><path d="m9 12 3 3 7-7"></path></svg>
                        <span>{{ app()->getLocale() === 'fr' ? 'Tout marquer comme lu' : translate('Mark all as read') }}</span>
                    </button>
                </div>
                <div class="mayush-notification-dropdown__body">
                    <p class="mayush-notification-state" data-notification-loading>{{ translate('Loading notifications') }}</p>
                    <p class="mayush-notification-state" data-notification-empty hidden>{{ translate('No notifications found') }}</p>
                    <p class="mayush-notification-state" data-notification-error hidden>{{ translate('Notifications are temporarily unavailable') }}</p>
                    <div class="mayush-notification-groups" data-notification-groups aria-live="polite"></div>
                </div>
                <div class="mayush-notification-dropdown__footer">
                    <a href="{{ $notificationInboxUrl }}" data-notification-view-all>{{ translate('View all notifications') }}</a>
                </div>
            </section>
        </div>
    @else
        <a class="mayush-notification-fallback"
           href="{{ $notificationInboxUrl }}"
           aria-label="{{ translate('Notifications') }}">
            <svg class="mayush-notification-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                <path d="M10 21h4"></path>
            </svg>
        </a>
    @endif
@endauth

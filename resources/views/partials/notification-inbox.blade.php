@php
    $notificationInboxVariant = $notificationInboxVariant ?? 'buyer';
    $notificationInboxTitle = $notificationInboxTitle ?? translate('Notifications');
    $notificationBulkDeleteRoute = $notificationBulkDeleteRoute ?? '#';
    $notificationUnreadCount = $notificationUnreadCount ?? 0;
    $notificationIsFrench = app()->getLocale() === 'fr';
    $notificationMarkAllLabel = $notificationIsFrench ? 'Tout marquer comme lu' : translate('Mark all as read');
    $notificationArchiveLabel = $notificationIsFrench ? 'Archiver la sélection' : translate('Archive selection');
    $notificationSelectionLabel = $notificationIsFrench ? 'Sélectionner tout' : translate('Select all');
    $notificationSelectedLabel = $notificationIsFrench ? 'sélectionnée(s)' : translate('selected');
@endphp

@once
    <style>
        .mayush-notification-inbox {
            /* Mayush Design palette — shared by buyer, seller, and admin surfaces. */
            --notification-accent: var(--mayush-orange, #D97434);
            --notification-accent-hover: var(--mayush-orange-hover, #C46524);
            --notification-accent-rgb: 217, 116, 52;
            --notification-surface: var(--mayush-beige, #F5F1E8);
            --notification-surface-alt: var(--mayush-beige-alt, #F0E8DD);
            --notification-white: var(--mayush-white, #FFFFFF);
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
            background: var(--notification-white);
            border: 1px solid var(--notification-border);
            border-radius: var(--mayush-radius-2xl, 16px);
            box-shadow: var(--mayush-shadow-card, 0 2px 8px rgba(0, 0, 0, .08));
            color: var(--notification-text);
            overflow: hidden;
        }

        .mayush-notification-inbox--admin {
            --notification-surface: var(--mayush-beige, #F5F1E8);
        }

        .mayush-notification-inbox--seller {
            --notification-surface: var(--mayush-beige, #F5F1E8);
        }

        .mayush-notification-inbox__header {
            align-items: flex-start;
            display: flex;
            gap: 14px;
            justify-content: space-between;
            padding: 24px 26px 20px;
        }

        .mayush-notification-inbox__heading {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
        }

        .mayush-notification-inbox__heading-icon {
            align-items: center;
            background: rgba(var(--notification-accent-rgb), .1);
            border-radius: 12px;
            color: var(--notification-accent);
            display: inline-flex;
            flex: 0 0 auto;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .mayush-notification-inbox__heading-icon svg,
        .mayush-notification-inbox__action svg,
        .mayush-notification-inbox__bulk-action svg,
        .mayush-notification-inbox .mayush-notification-item__icon svg {
            fill: none;
            height: 20px;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.9;
            width: 20px;
        }

        .mayush-notification-inbox__title {
            color: var(--notification-text);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.2;
            margin: 0;
        }

        .mayush-notification-inbox__subtitle {
            color: var(--notification-muted);
            font-size: 13px;
            line-height: 1.45;
            margin: 4px 0 0;
        }

        .mayush-notification-inbox__unread-count {
            align-items: center;
            background: rgba(var(--notification-accent-rgb), .1);
            border-radius: 999px;
            color: var(--notification-accent);
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            margin-left: 8px;
            padding: 7px 9px;
            white-space: nowrap;
        }

        .mayush-notification-inbox__toolbar {
            background: var(--notification-surface);
            border-bottom: 1px solid var(--notification-border);
            border-top: 1px solid var(--notification-border);
            padding: 16px 26px;
        }

        .mayush-notification-filter-form {
            align-items: end;
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(145px, 1.1fr) minmax(130px, 1fr) minmax(120px, .85fr) auto;
        }

        .mayush-notification-filter-field {
            min-width: 0;
        }

        .mayush-notification-filter-field label {
            color: var(--notification-muted);
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .03em;
            margin: 0 0 6px;
            text-transform: uppercase;
        }

        .mayush-notification-filter-field .form-control {
            background-color: var(--notification-white);
            border-color: var(--notification-border);
            border-radius: var(--mayush-radius-md, 6px);
            box-shadow: none;
            color: var(--notification-text);
            font-size: 13px;
            height: 40px;
            padding-left: 11px;
            padding-right: 28px;
        }

        .mayush-notification-filter-field .form-control:focus {
            border-color: var(--notification-accent);
            box-shadow: 0 0 0 3px rgba(var(--notification-accent-rgb), .14);
        }

        .mayush-notification-filter-submit,
        .mayush-notification-filter-reset,
        .mayush-notification-inbox__action,
        .mayush-notification-inbox__bulk-action,
        .mayush-notification-inbox .mayush-notification-item__read-action {
            align-items: center;
            border-radius: var(--mayush-radius-md, 6px);
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            font-weight: 800;
            gap: 7px;
            justify-content: center;
            min-height: 40px;
            text-decoration: none;
            transition: background-color 180ms ease, border-color 180ms ease, box-shadow 180ms ease, color 180ms ease;
            white-space: nowrap;
        }

        .mayush-notification-filter-submit {
            background: var(--notification-accent);
            border: 1px solid var(--notification-accent);
            color: var(--notification-white);
            padding: 0 16px;
        }

        .mayush-notification-filter-submit:hover {
            background: var(--notification-accent-hover);
            border-color: var(--notification-accent-hover);
            color: var(--notification-white);
        }

        .mayush-notification-filter-reset {
            color: var(--notification-muted);
            margin-left: 2px;
            padding: 0 8px;
        }

        .mayush-notification-filter-reset:hover {
            color: var(--notification-accent);
            text-decoration: none;
        }

        .mayush-notification-filter-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .mayush-notification-inbox__action {
            background: var(--notification-white);
            border: 1px solid var(--notification-border);
            color: var(--notification-text);
            padding: 0 12px;
        }

        .mayush-notification-inbox__action--primary {
            border-color: rgba(var(--notification-accent-rgb), .42);
            color: var(--notification-accent);
        }

        .mayush-notification-inbox__action:hover {
            background: rgba(var(--notification-accent-rgb), .07);
            border-color: var(--notification-accent);
            color: var(--notification-accent);
            text-decoration: none;
        }

        .mayush-notification-selection {
            align-items: center;
            background: rgba(var(--notification-accent-rgb), .07);
            border-bottom: 1px solid rgba(var(--notification-accent-rgb), .16);
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            justify-content: space-between;
            padding: 12px 26px;
        }

        .mayush-notification-selection__all {
            align-items: center;
            color: var(--notification-text);
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 9px;
            margin: 0;
        }

        .mayush-notification-selection__all input,
        .mayush-notification-inbox .mayush-notification-item__check input {
            accent-color: var(--notification-accent);
            height: 17px;
            margin: 0;
            width: 17px;
        }

        .mayush-notification-selection__controls {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .mayush-notification-selection__count {
            color: var(--notification-muted);
            font-size: 13px;
            font-weight: 700;
        }

        .mayush-notification-inbox__bulk-action {
            background: var(--notification-accent);
            border: 1px solid var(--notification-accent);
            color: var(--notification-white);
            min-height: 36px;
            padding: 0 12px;
        }

        .mayush-notification-inbox__bulk-action:hover:not(:disabled) {
            background: var(--notification-accent-hover);
            border-color: var(--notification-accent-hover);
        }

        .mayush-notification-inbox__bulk-action:disabled {
            cursor: not-allowed;
            opacity: .45;
        }

        .mayush-notification-inbox .mayush-notification-list {
            list-style: none;
            margin: 0;
            padding: 8px 16px;
        }

        .mayush-notification-inbox .mayush-notification-item {
            align-items: flex-start;
            border: 1px solid transparent;
            border-radius: 12px;
            display: grid;
            gap: 12px;
            grid-template-columns: auto auto minmax(0, 1fr) auto;
            margin: 2px 0;
            padding: 15px 10px;
            transition: background-color 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
        }

        .mayush-notification-inbox .mayush-notification-item:hover {
            background: var(--notification-surface);
            border-color: var(--notification-border);
        }

        .mayush-notification-inbox .mayush-notification-item--unread {
            background: rgba(var(--notification-accent-rgb), .055);
            border-color: rgba(var(--notification-accent-rgb), .14);
        }

        .mayush-notification-inbox .mayush-notification-item--unread:hover {
            background: rgba(var(--notification-accent-rgb), .09);
            border-color: rgba(var(--notification-accent-rgb), .25);
            box-shadow: var(--mayush-shadow-card, 0 2px 8px rgba(0, 0, 0, .08));
        }

        .mayush-notification-inbox .mayush-notification-item__check {
            cursor: pointer;
            line-height: 1;
            margin: 7px 0 0;
        }

        .mayush-notification-inbox .mayush-notification-item__icon {
            align-items: center;
            background: var(--notification-surface-alt);
            border-radius: var(--mayush-radius-lg, 8px);
            color: var(--notification-muted);
            display: inline-flex;
            height: 38px;
            justify-content: center;
            margin-top: 1px;
            width: 38px;
        }

        .mayush-notification-inbox .mayush-notification-item__icon--orders,
        .mayush-notification-inbox .mayush-notification-item__icon--payments,
        .mayush-notification-inbox .mayush-notification-item__icon--payouts {
            background: rgba(var(--notification-accent-rgb), .12);
            color: var(--notification-accent);
        }

        .mayush-notification-inbox .mayush-notification-item__icon--security,
        .mayush-notification-inbox .mayush-notification-item__icon--refunds {
            background: rgba(var(--notification-danger-rgb), .10);
            color: var(--notification-danger);
        }

        .mayush-notification-inbox .mayush-notification-item__icon--products {
            background: rgba(0, 168, 107, .10);
            color: var(--notification-success);
        }

        .mayush-notification-inbox .mayush-notification-item__icon--messages,
        .mayush-notification-inbox .mayush-notification-item__icon--account,
        .mayush-notification-inbox .mayush-notification-item__icon--seller {
            background: rgba(21, 101, 192, .10);
            color: var(--notification-info);
        }

        .mayush-notification-inbox .mayush-notification-item__icon--marketing {
            background: rgba(var(--notification-warning-rgb), .16);
            color: var(--mayush-price, #9F4F18);
        }

        .mayush-notification-inbox .mayush-notification-item__content {
            min-width: 0;
        }

        .mayush-notification-inbox .mayush-notification-item__topline {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 7px 9px;
            margin-bottom: 5px;
        }

        .mayush-notification-inbox .mayush-notification-item__title {
            color: var(--notification-text);
            font-size: 13px;
            font-weight: 800;
            line-height: 1.3;
        }

        .mayush-notification-inbox .mayush-notification-item__status-dot {
            background: var(--notification-accent);
            border-radius: 999px;
            display: inline-block;
            height: 7px;
            width: 7px;
        }

        .mayush-notification-inbox .mayush-notification-item__message,
        .mayush-notification-inbox .mayush-notification-item__message a {
            color: var(--notification-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .mayush-notification-inbox .mayush-notification-item--unread .mayush-notification-item__message,
        .mayush-notification-inbox .mayush-notification-item--unread .mayush-notification-item__message a {
            color: var(--notification-text);
        }

        .mayush-notification-inbox .mayush-notification-item__message a {
            color: var(--notification-accent);
            font-weight: 800;
            text-decoration: none;
        }

        .mayush-notification-inbox .mayush-notification-item__message a:hover {
            text-decoration: underline;
        }

        .mayush-notification-inbox .mayush-notification-item__meta {
            align-items: center;
            color: var(--notification-muted-light);
            display: flex;
            flex-wrap: wrap;
            font-size: 11.5px;
            gap: 7px;
            margin-top: 8px;
        }

        .mayush-notification-inbox .mayush-notification-item__category {
            background: var(--notification-surface-alt);
            border-radius: var(--mayush-radius-full, 999px);
            color: var(--notification-muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .035em;
            padding: 4px 7px;
            text-transform: uppercase;
        }

        .mayush-notification-inbox .mayush-notification-item__category--critical {
            background: rgba(var(--notification-danger-rgb), .10);
            color: var(--notification-danger);
        }

        .mayush-notification-inbox .mayush-notification-item__category--important {
            background: rgba(var(--notification-warning-rgb), .16);
            color: var(--mayush-price, #9F4F18);
        }

        .mayush-notification-inbox .mayush-notification-item__signal {
            align-items: center;
            background: var(--notification-surface-alt);
            border-radius: var(--mayush-radius-full, 999px);
            display: inline-flex;
            font-size: 10px;
            font-weight: 800;
            gap: 4px;
            letter-spacing: .025em;
            line-height: 1;
            padding: 4px 7px;
            white-space: nowrap;
        }

        .mayush-notification-inbox .mayush-notification-item__signal svg {
            fill: none;
            height: 12px;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
            width: 12px;
        }

        .mayush-notification-inbox .mayush-notification-item__signal--priority-info {
            color: var(--notification-info);
        }

        .mayush-notification-inbox .mayush-notification-item__signal--priority-important {
            background: rgba(var(--notification-warning-rgb), .16);
            color: var(--mayush-price, #9F4F18);
        }

        .mayush-notification-inbox .mayush-notification-item__signal--priority-critical {
            background: rgba(var(--notification-danger-rgb), .10);
            color: var(--notification-danger);
        }

        .mayush-notification-inbox .mayush-notification-item__signal--status-unread {
            background: var(--mayush-soft-orange, rgba(217, 116, 52, .10));
            color: var(--notification-accent);
        }

        .mayush-notification-inbox .mayush-notification-item__signal--status-read {
            color: var(--notification-success);
        }

        .mayush-notification-inbox .mayush-notification-item__read-action {
            background: transparent;
            border: 0;
            color: var(--notification-accent);
            font-size: 12px;
            min-height: auto;
            padding: 6px;
        }

        .mayush-notification-inbox .mayush-notification-item__read-action:hover {
            background: rgba(var(--notification-accent-rgb), .09);
        }

        .mayush-notification-inbox__empty {
            color: var(--notification-muted);
            padding: 52px 18px;
            text-align: center;
        }

        .mayush-notification-inbox__pagination {
            border-top: 1px solid var(--notification-border);
            padding: 18px 26px 22px;
        }

        .mayush-notification-inbox :focus-visible {
            outline: 3px solid rgba(var(--notification-accent-rgb), .42);
            outline-offset: 2px;
        }

        @media (max-width: 767.98px) {
            .mayush-notification-inbox {
                border-radius: 13px;
            }

            .mayush-notification-inbox__header,
            .mayush-notification-inbox__toolbar,
            .mayush-notification-selection,
            .mayush-notification-inbox__pagination {
                padding-left: 16px;
                padding-right: 16px;
            }

            .mayush-notification-inbox__header {
                padding-bottom: 16px;
                padding-top: 18px;
            }

            .mayush-notification-filter-form {
                grid-template-columns: 1fr 1fr;
            }

            .mayush-notification-filter-submit {
                width: 100%;
            }

            .mayush-notification-inbox .mayush-notification-item {
                gap: 10px;
                grid-template-columns: auto auto minmax(0, 1fr);
                padding: 14px 6px;
            }

            .mayush-notification-inbox .mayush-notification-item__read-action {
                grid-column: 3;
                justify-self: start;
                padding-left: 0;
            }
        }

        @media (max-width: 440px) {
            .mayush-notification-inbox__header {
                align-items: stretch;
                flex-direction: column;
            }

            .mayush-notification-filter-form {
                grid-template-columns: 1fr;
            }

            .mayush-notification-inbox__heading-icon {
                height: 38px;
                width: 38px;
            }

            .mayush-notification-inbox__title {
                font-size: 18px;
            }

            .mayush-notification-inbox .mayush-notification-list {
                padding-left: 10px;
                padding-right: 10px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .mayush-notification-inbox *,
            .mayush-notification-inbox *::before,
            .mayush-notification-inbox *::after {
                scroll-behavior: auto !important;
                transition-duration: 0ms !important;
            }
        }
    </style>
@endonce

<section class="mayush-notification-inbox mayush-notification-inbox--{{ $notificationInboxVariant }}"
         data-notification-inbox
         data-bulk-delete-url="{{ $notificationBulkDeleteRoute }}"
         aria-labelledby="notification-inbox-title">
    <header class="mayush-notification-inbox__header">
        <div class="mayush-notification-inbox__heading">
            <span class="mayush-notification-inbox__heading-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg>
            </span>
            <div>
                <h1 id="notification-inbox-title" class="mayush-notification-inbox__title">{{ $notificationInboxTitle }}</h1>
                <p class="mayush-notification-inbox__subtitle">
                    {{ $notificationIsFrench ? 'Suivez vos commandes, paiements et activités importantes.' : translate('Stay up to date with your orders, payments, and important activity.') }}
                    <span class="mayush-notification-inbox__unread-count" data-notification-unread-count>{{ $notificationUnreadCount }}</span>
                </p>
            </div>
        </div>
    </header>

    <div class="mayush-notification-inbox__toolbar">
        @include('partials.notification-filters', ['notificationInboxVariant' => $notificationInboxVariant, 'notificationMarkAllLabel' => $notificationMarkAllLabel])
    </div>

    @if($notifications->count())
        <div class="mayush-notification-selection" data-notification-selection-bar>
            <label class="mayush-notification-selection__all">
                <input type="checkbox" class="check-all" data-notification-select-all>
                <span>{{ $notificationSelectionLabel }}</span>
            </label>
            <div class="mayush-notification-selection__controls">
                <span class="mayush-notification-selection__count" data-notification-selection-count aria-live="polite">0 {{ $notificationSelectedLabel }}</span>
                <button type="button" class="mayush-notification-inbox__bulk-action" data-notification-bulk-action disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"></path><path d="M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"></path></svg>
                    <span>{{ $notificationArchiveLabel }}</span>
                </button>
            </div>
        </div>
    @endif

    <ul class="mayush-notification-list" aria-label="{{ translate('Notifications') }}">
        <x-notification :notifications="$notifications" />
    </ul>

    @if($notifications->hasPages())
        <div class="mayush-notification-inbox__pagination">
            {{ $notifications->links() }}
        </div>
    @endif
</section>

@include('partials.notification-inbox-script')

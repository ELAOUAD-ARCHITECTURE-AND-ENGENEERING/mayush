@php
    $notificationIsFrench = app()->getLocale() === 'fr';
    $notificationHasActiveFilters = request()->filled('category') || request()->filled('severity') || request()->filled('read');
    $notificationMarkAllLabel = $notificationMarkAllLabel ?? ($notificationIsFrench ? 'Tout marquer comme lu' : translate('Mark all as read'));
    $notificationCategoryLabels = [
        'orders' => $notificationIsFrench ? 'Commandes' : translate('Orders'),
        'payments' => $notificationIsFrench ? 'Paiements' : translate('Payments'),
        'refunds' => $notificationIsFrench ? 'Remboursements' : translate('Refunds'),
        'security' => $notificationIsFrench ? 'Sécurité' : translate('Security'),
        'seller' => $notificationIsFrench ? 'Vendeur' : translate('Seller'),
        'payouts' => $notificationIsFrench ? 'Versements' : translate('Payouts'),
        'account' => $notificationIsFrench ? 'Compte' : translate('Account'),
        'messages' => $notificationIsFrench ? 'Messages' : translate('Messages'),
        'products' => $notificationIsFrench ? 'Produits' : translate('Products'),
        'marketing' => $notificationIsFrench ? 'Marketing' : translate('Marketing'),
    ];
    $notificationPriorityLabel = $notificationIsFrench ? 'Priorité' : translate('Priority');
    $notificationPreferencesLabel = $notificationIsFrench ? 'Préférences' : translate('Preferences');
    $notificationUnreadLabel = $notificationIsFrench ? 'Non lu' : translate('Unread');
    $notificationReadLabel = $notificationIsFrench ? 'Lu' : translate('Read');
@endphp

<form method="GET" class="mayush-notification-filter-form" aria-label="{{ translate('Filter notifications') }}">
    <div class="mayush-notification-filter-field">
        <label for="notification-category">{{ translate('Category') }}</label>
        <select id="notification-category" name="category" class="form-control">
            <option value="">{{ translate('All') }}</option>
            @foreach(['orders', 'payments', 'refunds', 'security', 'seller', 'payouts', 'account', 'messages', 'products', 'marketing'] as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $notificationCategoryLabels[$category] }}</option>
            @endforeach
        </select>
    </div>
    <div class="mayush-notification-filter-field">
        <label for="notification-severity">{{ $notificationPriorityLabel }}</label>
        <select id="notification-severity" name="severity" class="form-control">
            <option value="">{{ translate('All') }}</option>
            @foreach(['critical', 'important', 'info'] as $severity)
                <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ translate(ucfirst($severity)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="mayush-notification-filter-field">
        <label for="notification-read">{{ translate('Status') }}</label>
        <select id="notification-read" name="read" class="form-control">
            <option value="">{{ translate('All') }}</option>
            <option value="unread" @selected(request('read') === 'unread')>{{ $notificationUnreadLabel }}</option>
            <option value="read" @selected(request('read') === 'read')>{{ $notificationReadLabel }}</option>
        </select>
    </div>
    <div class="d-flex align-items-center">
        <button class="mayush-notification-filter-submit" type="submit">{{ translate('Filter') }}</button>
        @if($notificationHasActiveFilters)
            <a href="{{ url()->current() }}" class="mayush-notification-filter-reset">{{ translate('Clear') }}</a>
        @endif
    </div>
</form>

@if(config('notifications_v2.enabled'))
    <div class="mayush-notification-filter-actions">
        <button type="button" class="mayush-notification-inbox__action mayush-notification-inbox__action--primary" data-notification-read-all>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 12 4 4L20 4"></path><path d="m9 12 3 3 7-7"></path></svg>
            <span>{{ $notificationMarkAllLabel }}</span>
        </button>
        @if(Route::has('notification-preferences.show'))
            <a href="{{ route('notification-preferences.show') }}" class="mayush-notification-inbox__action">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4"></path><path d="M7 5v4M15 10v4M11 15v4"></path></svg>
                <span>{{ $notificationPreferencesLabel }}</span>
            </a>
        @endif
    </div>
@endif

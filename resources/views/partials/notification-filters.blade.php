<div class="d-flex flex-wrap align-items-end justify-content-between mb-3" style="gap: .75rem;">
    <form method="GET" class="d-flex flex-wrap align-items-end" style="gap: .5rem;">
        <div>
            <label class="small text-muted d-block mb-1" for="notification-category">{{ translate('Category') }}</label>
            <select id="notification-category" name="category" class="form-control form-control-sm">
                <option value="">{{ translate('All') }}</option>
                @foreach(['orders', 'payments', 'refunds', 'security', 'seller', 'payouts', 'account', 'messages', 'products', 'marketing'] as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ translate(ucfirst($category)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="small text-muted d-block mb-1" for="notification-severity">{{ translate('Priority') }}</label>
            <select id="notification-severity" name="severity" class="form-control form-control-sm">
                <option value="">{{ translate('All') }}</option>
                @foreach(['critical', 'important', 'info'] as $severity)
                    <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ translate(ucfirst($severity)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="small text-muted d-block mb-1" for="notification-read">{{ translate('Status') }}</label>
            <select id="notification-read" name="read" class="form-control form-control-sm">
                <option value="">{{ translate('All') }}</option>
                <option value="unread" @selected(request('read') === 'unread')>{{ translate('Unread') }}</option>
                <option value="read" @selected(request('read') === 'read')>{{ translate('Read') }}</option>
            </select>
        </div>
        <button class="btn btn-sm btn-primary" type="submit">{{ translate('Filter') }}</button>
    </form>
    <button type="button" class="btn btn-sm btn-outline-primary" data-notification-read-all>
        <i class="las la-check-double mr-1"></i>{{ translate('Mark all as read') }}
    </button>
    <a href="{{ route('notification-preferences.show') }}" class="btn btn-sm btn-outline-secondary">
        <i class="las la-sliders-h mr-1"></i>{{ translate('Preferences') }}
    </a>
</div>

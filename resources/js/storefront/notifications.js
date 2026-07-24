const runtime = document.querySelector('meta[name="notification-center"]');

/**
 * Shared non-blocking toast API for Mayush's Blade and AIZ flows.
 *
 * document.dispatchEvent(new CustomEvent('mayush:toast', {
 *     detail: {
 *         title: 'Order updated',
 *         message: 'Order 12345 is on its way.',
 *         category: 'orders',
 *         severity: 'important'
 *     }
 * }));
 */
if (runtime) {
    const userId = runtime.dataset.userId;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const summaryUrl = runtime.dataset.summaryUrl;
    const readTemplate = runtime.dataset.readUrl;
    const ackTemplate = runtime.dataset.ackUrl;
    const tabs = 'BroadcastChannel' in window ? new BroadcastChannel('mayush-notifications') : null;

    const centers = () => Array.from(document.querySelectorAll('[data-notification-center]'));
    const notificationText = (value, fallback = '') => typeof value === 'string' && value.trim() !== ''
        ? value.trim()
        : fallback;
    const notificationCount = (value) => Math.max(0, Number.parseInt(value, 10) || 0);
    const notificationCategory = (value, eventKey = '') => {
        const category = notificationText(value, 'system').toLowerCase();
        if (['orders', 'payments', 'refunds', 'security', 'seller', 'payouts', 'account', 'messages', 'products', 'marketing'].includes(category)) {
            return category;
        }

        const key = notificationText(eventKey).toLowerCase();
        if (key.startsWith('order.')) return 'orders';
        if (key.startsWith('payment.')) return 'payments';
        if (key.startsWith('refund.') || key.startsWith('dispute.') || key.startsWith('chargeback.')) return 'refunds';
        if (key.startsWith('security.') || key.startsWith('login.')) return 'security';
        if (key.startsWith('seller.')) return 'seller';
        if (key.startsWith('payout.')) return 'payouts';
        if (key.startsWith('account.')) return 'account';
        if (key.startsWith('message.')) return 'messages';
        if (key.startsWith('product.') || key.startsWith('stock.') || key.startsWith('restock.')) return 'products';
        if (key.startsWith('marketing.') || key.startsWith('campaign.')) return 'marketing';

        return 'system';
    };
    const notificationEventIconKey = (notification, category) => {
        const orderStatusIcons = {
            placed: 'order-placed', confirmed: 'order-confirmed', processed: 'order-confirmed', processing: 'order-confirmed',
            cancelled: 'order-cancelled', canceled: 'order-cancelled', shipped: 'order-shipped', on_delivery: 'order-shipped',
            on_the_way: 'order-shipped', in_transit: 'order-shipped', out_for_delivery: 'order-shipped', delivered: 'order-delivered',
        };
        const eventKey = notificationText(notification?.event_key).toLowerCase();
        const status = notificationText(notification?.status || notification?.legacy_data?.status)
            .toLowerCase()
            .replace(/[ -]/g, '_');

        if (eventKey === 'order.placed' && orderStatusIcons[status]) return orderStatusIcons[status];

        return ({
            'order.placed': 'order-placed', 'order.confirmed': 'order-confirmed', 'order.cancelled': 'order-cancelled',
            'order.shipped': 'order-shipped', 'order.delivered': 'order-delivered', 'order.updated': 'order-updated',
            'payment.approved': 'payment-approved', 'payment.success': 'payment-approved', 'payment.failed': 'payment-failed',
            'refund.requested': 'refund-requested', 'refund.approved': 'refund-approved', 'refund.rejected': 'refund-rejected',
            'dispute.updated': 'dispute-updated', 'security.alert': 'security-alert', 'security.login': 'security-login',
            'seller.status': 'seller-status', 'payout.status': 'payout-status', 'account.changed': 'account-changed',
            'message.received': 'message-received', 'product.status': 'product-status', 'product.restocked': 'product-restocked',
            'stock.alert': 'stock-alert', 'marketing.promotion': 'marketing-promotion', 'marketing.newsletter': 'marketing-newsletter',
            'marketing.recommendation': 'marketing-recommendation', 'custom.sent': 'custom-sent',
        })[eventKey] || category;
    };
    const notificationSeverity = (value) => {
        const severity = notificationText(value, 'info').toLowerCase();
        return ['info', 'important', 'critical'].includes(severity) ? severity : 'info';
    };
    const signalLabel = (type, key) => runtime.dataset[`${type}${key.charAt(0).toUpperCase()}${key.slice(1)}Label`]
        || `${key.charAt(0).toUpperCase()}${key.slice(1)}`;

    const formatTimestamp = (value) => {
        const date = value ? new Date(value) : null;
        if (!date || Number.isNaN(date.getTime())) return '';

        return new Intl.DateTimeFormat(document.documentElement.lang || undefined, {
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    };

    const safeActionUrl = (value) => {
        if (!value || typeof value !== 'string') return null;

        try {
            const url = new URL(value, window.location.origin);
            return url.origin === window.location.origin && /^https?:$/.test(url.protocol)
                ? url.href
                : null;
        } catch {
            return null;
        }
    };

    const setElementHidden = (element, hidden) => {
        if (element) element.hidden = hidden;
    };

    const renderUnreadCount = (count) => {
        const unreadCount = notificationCount(count);

        document.querySelectorAll('[data-notification-unread-count], .notification-count')
            .forEach((element) => {
                element.textContent = String(unreadCount);
                element.hidden = unreadCount === 0;
                element.setAttribute('aria-hidden', 'true');
            });

        document.querySelectorAll('[data-notification-unread-label]').forEach((element) => {
            element.textContent = unreadCount === 1
                ? '1 unread notification'
                : `${unreadCount} unread notifications`;
        });

    };

    const notificationGroup = (notification) => {
        const date = notification?.created_at ? new Date(notification.created_at) : null;
        if (!date || Number.isNaN(date.getTime())) return 'Earlier';

        const now = new Date();
        return date.toDateString() === now.toDateString() ? 'Today' : 'Earlier';
    };

    const notificationIconPath = (category) => ({
        'order-placed': 'M3 4h2l2 11h10l2-8H6m2 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2m8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2',
        'order-confirmed': 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5m-11 8 2 2 4-4',
        'order-cancelled': 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5m-11 6 6 6m0-6-6 6',
        'order-shipped': 'M3 7h11v9H3V7Zm11 4h3l3 3v2h-6v-5ZM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4m10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4',
        'order-delivered': 'm3 11 9-7 9 7v9H3v-9Zm6 9v-5h6v5m-4-2 2 2 4-4',
        'order-updated': 'M20 11a8 8 0 1 0 2 5m-2-5V5m0 6h-6',
        'payment-approved': 'M4 5h16v14H4V5Zm0 5h16m-8 4 2 2 4-4',
        'payment-failed': 'M4 5h16v14H4V5Zm0 5h16m8 4-4 4m0-4 4 4',
        'refund-requested': 'M7 7H4v4m0 0 3-3a6 6 0 1 1-1 7m7-5v4l2.5 1.5',
        'refund-approved': 'M7 7H4v4m0 0 3-3a6 6 0 1 1-1 7m7-3 2 2 4-4',
        'refund-rejected': 'M7 7H4v4m0 0 3-3a6 6 0 1 1-1 7m7-3 4 4m0-4-4 4',
        'dispute-updated': 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm0 5v5m0 3h.01',
        'security-alert': 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm0 5v5m0 3h.01',
        'security-login': 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm0 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4m-3 4a3 3 0 0 1 6 0',
        'seller-status': 'M4 10h16M5 10v9h14v-9m-14 0 2-5h10l2 5m-10 5 2 2 4-4',
        'payout-status': 'M5 7h14v10H5zM8 11h.01M12 11h4m-4 4v-2l2 1',
        'account-changed': 'M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6M5 21a7 7 0 0 1 14 0m0-13 2 2-2 2',
        'message-received': 'M5 5h14v10H9l-4 4V5Zm3 4h8m-8 3h5',
        'product-status': 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5M12 12v9',
        'product-restocked': 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5m0 7v-4m-2 2h4',
        'stock-alert': 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 6v4m0 3h.01',
        'marketing-promotion': 'm4 12 14-6v12L4 12Zm0 0v5m3-3 2 5h3l-2-6',
        'marketing-newsletter': 'M5 4h14v16H5V4Zm3 4h8m-8 4h8m-8 4h5',
        'marketing-recommendation': 'm12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z',
        'custom-sent': 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4',
        orders: 'M6 7h12l-1 13H7L6 7Zm3 0V5a3 3 0 0 1 6 0v2M4 7h16',
        payments: 'M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm6 4v8m3-6.5c-.4-.4-1.1-.6-1.8-.6-1.2 0-2.2.7-2.2 1.7 0 2.6 4.2 1.3 4.2 3.8 0 1-.9 1.7-2.2 1.7-.8 0-1.5-.3-2-.8',
        security: 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm-2.5 9 1.7 1.7 3.8-3.8',
        refunds: 'M8 7H4v4m0 0 3-3a6 6 0 1 1-1 7',
        payouts: 'M5 7h14v10H5zM8 11h.01M12 11h4M8 15h8',
        seller: 'M4 10h16M5 10v9h14v-9m-14 0 2-5h10l2 5M9 19v-5h6v5',
        account: 'M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6M5 21a7 7 0 0 1 14 0',
        messages: 'M5 5h14v10H9l-4 4V5Zm3 4h8m-8 3h5',
        products: 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5M12 12v9',
        marketing: 'm4 12 14-6v12L4 12Zm0 0v5m3-3 2 5h3l-2-6',
    })[category] || 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4';

    const notificationSignalPath = (type, value) => {
        if (type === 'priority') {
            return value === 'critical'
                ? 'M12 3 5 6v5c0 4.5 3 7.8 7 10 4-2.2 7-5.5 7-10V6l-7-3Zm0 5v5m0 3h.01'
                : value === 'important'
                    ? 'M12 3 3 20h18L12 3Zm0 6v4m0 4h.01'
                    : 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm0 8v5m0-8h.01';
        }

        return value === 'read'
            ? 'm5 12 4 4L19 6'
            : 'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 4v4l2.5 1.5';
    };

    const createSvgIcon = (pathData) => {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        svg.setAttribute('viewBox', '0 0 24 24');
        path.setAttribute('d', pathData);
        svg.append(path);
        return svg;
    };

    const createNotificationIcon = (iconKey, category) => {
        const icon = document.createElement('span');
        const categoryKey = notificationCategory(category);

        icon.className = `mayush-notification-item__icon mayush-notification-item__icon--${categoryKey} mayush-notification-item__icon--event-${iconKey}`;
        icon.setAttribute('aria-hidden', 'true');
        icon.append(createSvgIcon(notificationIconPath(iconKey)));
        return icon;
    };

    const createNotificationSignal = (type, value) => {
        const signal = document.createElement('span');
        const label = document.createElement('span');

        signal.className = `mayush-notification-dropdown__signal mayush-notification-dropdown__signal--${type}-${value}`;
        signal.setAttribute('aria-label', signalLabel(type, value));
        signal.append(createSvgIcon(notificationSignalPath(type, value)));
        label.textContent = signalLabel(type, value);
        signal.append(label);
        return signal;
    };

    const createNotificationItem = (notification) => {
        const actionUrl = safeActionUrl(notification?.action_url);
        const unread = !notification?.read_at;
        const category = notificationCategory(notification?.category, notification?.event_key);
        const eventIconKey = notificationEventIconKey(notification, category);
        const severity = notificationSeverity(notification?.severity);
        const status = unread ? 'unread' : 'read';
        const item = document.createElement(actionUrl ? 'a' : 'button');

        if (actionUrl) {
            item.href = actionUrl;
        } else {
            item.type = 'button';
        }

        item.className = `mayush-notification-item mayush-notification-item--priority-${severity} mayush-notification-item--status-${status}${unread ? ' mayush-notification-item--unread' : ''}`;
        item.dataset.notificationItem = 'true';
        item.dataset.notificationId = notificationText(notification?.id);
        item.setAttribute('role', 'listitem');
        item.setAttribute('aria-label', `${notificationText(notification?.title, 'Notification')}. ${signalLabel('priority', severity)}. ${signalLabel('status', status)}.`);

        const indicator = document.createElement('span');
        indicator.className = 'mayush-notification-item__indicator';
        indicator.setAttribute('aria-hidden', 'true');

        const content = document.createElement('span');
        const titleLine = document.createElement('span');
        const title = document.createElement('span');
        const message = document.createElement('span');
        const signals = document.createElement('span');
        const time = document.createElement('time');

        content.className = 'mayush-notification-item__content';
        titleLine.className = 'mayush-notification-item__title-line';
        title.className = 'mayush-notification-item__title';
        title.textContent = notificationText(notification?.title, 'Notification');
        message.className = 'mayush-notification-item__message';
        message.textContent = notificationText(notification?.message);
        signals.className = 'mayush-notification-dropdown__signals';
        signals.append(createNotificationSignal('priority', severity), createNotificationSignal('status', status));
        time.className = 'mayush-notification-item__time';
        time.dateTime = notificationText(notification?.created_at);
        time.textContent = formatTimestamp(notification?.created_at);

        titleLine.append(indicator, title);
        content.append(titleLine);
        if (message.textContent) content.append(message);
        content.append(signals);
        item.append(createNotificationIcon(eventIconKey, category), content, time);

        item.addEventListener('click', async (event) => {
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            if (actionUrl) event.preventDefault();
            const changed = unread ? await markRead(item.dataset.notificationId) : false;

            if (!actionUrl) return;
            if (changed || !item.dataset.notificationId) {
                window.location.assign(actionUrl);
                return;
            }

            // Navigation should still be possible when a transient read request fails.
            window.location.assign(actionUrl);
        });

        return item;
    };

    const renderSummary = (summary) => {
        const latest = Array.isArray(summary?.latest) ? summary.latest : [];
        renderUnreadCount(summary?.unread_count || 0);

        centers().forEach((center) => {
            const groups = center.querySelector('[data-notification-groups]');
            const loading = center.querySelector('[data-notification-loading]');
            const empty = center.querySelector('[data-notification-empty]');
            const error = center.querySelector('[data-notification-error]');
            if (!groups) return;

            const grouped = latest.reduce((result, notification) => {
                const group = notificationGroup(notification);
                result[group] ||= [];
                result[group].push(notification);
                return result;
            }, {});
            const fragments = ['Today', 'Earlier'].flatMap((label) => {
                if (!grouped[label]?.length) return [];
                const section = document.createElement('section');
                const heading = document.createElement('h3');
                const list = document.createElement('ul');
                section.className = 'mayush-notification-group';
                heading.className = 'mayush-notification-group__label';
                heading.textContent = label;
                list.className = 'mayush-notification-list';
                list.setAttribute('role', 'list');
                list.append(...grouped[label].map(createNotificationItem));
                section.append(heading, list);
                return [section];
            });

            groups.replaceChildren(...fragments);
            setElementHidden(loading, true);
            setElementHidden(empty, latest.length !== 0);
            setElementHidden(error, true);
        });
    };

    const renderError = () => {
        centers().forEach((center) => {
            setElementHidden(center.querySelector('[data-notification-loading]'), true);
            setElementHidden(center.querySelector('[data-notification-empty]'), true);
            setElementHidden(center.querySelector('[data-notification-error]'), false);
        });
    };

    const reconcile = async () => {
        if (!summaryUrl) return false;

        try {
            const response = await fetch(summaryUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Notification summary is unavailable.');

            const summary = await response.json();
            renderSummary(summary);
            document.dispatchEvent(new CustomEvent('mayush:notifications:summary', { detail: summary }));
            return true;
        } catch {
            renderError();
            return false;
        }
    };

    const markRead = async (id) => {
        if (!id || !readTemplate) return false;

        try {
            const response = await fetch(readTemplate.replace('__ID__', encodeURIComponent(id)), {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            });
            if (!response.ok) return false;

            document.dispatchEvent(new CustomEvent('mayush:notifications:changed'));
            return true;
        } catch {
            return false;
        }
    };

    const acknowledge = async (id) => {
        if (!id || !ackTemplate) return;

        try {
            await fetch(ackTemplate.replace('__ID__', encodeURIComponent(id)), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            });
        } catch {
            // A missing acknowledgement must never affect the durable inbox record.
        }
    };

    const closeDropdowns = (except = null, restoreFocus = false) => {
        centers().forEach((center) => {
            const dropdown = center.querySelector('[data-notification-dropdown]');
            const trigger = center.querySelector('[data-notification-trigger]');
            if (!dropdown || !trigger || center === except) return;
            const wasOpen = !dropdown.hidden;
            dropdown.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            if (restoreFocus && wasOpen) trigger.focus();
        });
    };

    const openDropdown = async (center) => {
        const dropdown = center.querySelector('[data-notification-dropdown]');
        const trigger = center.querySelector('[data-notification-trigger]');
        if (!dropdown || !trigger) return;

        closeDropdowns(center);
        dropdown.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        await reconcile();
    };

    const createToastRoot = () => {
        let root = document.querySelector('[data-mayush-toast-region]');
        if (root) return root;

        root = document.createElement('div');
        root.className = 'mayush-toast-region';
        root.dataset.mayushToastRegion = 'true';
        root.setAttribute('aria-live', 'polite');
        root.setAttribute('aria-relevant', 'additions');
        document.body.append(root);
        return root;
    };

    const showToast = (detail = {}) => {
        const severity = ['info', 'important', 'critical'].includes(detail.severity) ? detail.severity : 'info';
        const category = notificationText(detail.category, 'system')
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '');
        const toast = document.createElement('article');
        const body = document.createElement('div');
        const indicator = document.createElement('span');
        const title = document.createElement('strong');
        const message = document.createElement('p');
        const dismiss = document.createElement('button');

        toast.className = `mayush-toast mayush-toast--${severity} mayush-toast--category-${category}`;
        toast.setAttribute('role', severity === 'critical' ? 'alert' : 'status');
        indicator.className = 'mayush-toast__indicator';
        indicator.setAttribute('aria-hidden', 'true');
        body.className = 'mayush-toast__body';
        title.className = 'mayush-toast__title';
        message.className = 'mayush-toast__message';
        title.textContent = notificationText(detail.title, 'Notification');
        message.textContent = notificationText(detail.message);
        dismiss.className = 'mayush-toast__dismiss';
        dismiss.type = 'button';
        dismiss.setAttribute('aria-label', 'Dismiss notification');
        const closeIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        const closePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        closeIcon.setAttribute('viewBox', '0 0 24 24');
        closeIcon.setAttribute('width', '16');
        closeIcon.setAttribute('height', '16');
        closeIcon.setAttribute('aria-hidden', 'true');
        closePath.setAttribute('d', 'm6 6 12 12M18 6 6 18');
        closePath.setAttribute('fill', 'none');
        closePath.setAttribute('stroke', 'currentColor');
        closePath.setAttribute('stroke-width', '2');
        closePath.setAttribute('stroke-linecap', 'round');
        dismiss.append(closeIcon);

        body.append(title);
        if (message.textContent) body.append(message);
        toast.append(indicator, body, dismiss);

        const remove = () => toast.remove();
        dismiss.addEventListener('click', remove);
        createToastRoot().append(toast);

        if (severity !== 'critical') {
            window.setTimeout(remove, 6000);
        }
    };

    tabs?.addEventListener('message', (event) => {
        if (event.data === 'changed') reconcile();
    });

    document.addEventListener('mayush:toast', (event) => showToast(event.detail));
    document.addEventListener('mayush:notifications:changed', () => {
        tabs?.postMessage('changed');
        reconcile();
    });

    const toggleDropdown = (trigger) => {
        const center = trigger.closest('[data-notification-center]');
        const dropdown = center?.querySelector('[data-notification-dropdown]');
        if (!center || !dropdown) return;

        if (dropdown.hidden) {
            openDropdown(center);
        } else {
            closeDropdowns(null, true);
        }
    };

    // Storefront headers retain legacy click handlers. Bind directly to the
    // notification trigger so those handlers cannot swallow the interaction
    // before the dropdown is opened.
    document.querySelectorAll('[data-notification-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            toggleDropdown(trigger);
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-notification-center]')) {
            closeDropdowns();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const openCenter = centers().find((center) => !center.querySelector('[data-notification-dropdown]')?.hidden);
            if (openCenter) {
                event.preventDefault();
                closeDropdowns(null, true);
            }
            return;
        }

        if (event.key === 'ArrowDown' && event.target.matches('[data-notification-trigger]')) {
            const center = event.target.closest('[data-notification-center]');
            const dropdown = center?.querySelector('[data-notification-dropdown]');
            if (!dropdown || dropdown.hidden) return;
            const firstItem = dropdown.querySelector('[data-notification-item], [data-notification-view-all]');
            if (firstItem) {
                event.preventDefault();
                firstItem.focus();
            }
        }
    });

    reconcile();

    document.querySelectorAll('[data-notification-read-all]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                const response = await fetch('/notifications/read-all', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
                if (response.ok) {
                    document.dispatchEvent(new CustomEvent('mayush:notifications:changed'));
                    window.location.reload();
                }
            } finally {
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('[data-notification-toggle-read]').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.notificationId;
            const state = button.dataset.notificationState;
            button.disabled = true;
            try {
                const response = await fetch(`/notifications/${encodeURIComponent(id)}/${state}`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
                if (response.ok) {
                    document.dispatchEvent(new CustomEvent('mayush:notifications:changed'));
                    window.location.reload();
                }
            } finally {
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('[data-notification-open]').forEach((link) => {
        link.addEventListener('click', async (event) => {
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey) return;
            event.preventDefault();
            try {
                await markRead(link.dataset.notificationId);
            } finally {
                window.location.assign(link.href);
            }
        });
    });

    const initialiseBroadcasting = async () => {
        if (runtime.dataset.broadcasting !== '1' || !runtime.dataset.key || !runtime.dataset.host) return;

        try {
            const [{ default: Echo }, { default: Pusher }] = await Promise.all([
                import('laravel-echo'),
                import('pusher-js'),
            ]);
            window.Pusher = Pusher;
            const echo = new Echo({
                broadcaster: 'reverb',
                key: runtime.dataset.key,
                wsHost: runtime.dataset.host,
                wsPort: Number(runtime.dataset.port || 80),
                wssPort: Number(runtime.dataset.port || 443),
                forceTLS: runtime.dataset.scheme === 'https',
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            });

            echo.private(`App.Models.User.${userId}`)
                .listen('.notification.inbox.updated', (payload) => {
                    renderUnreadCount(payload?.unread_count || 0);
                    document.dispatchEvent(new CustomEvent('mayush:notification', { detail: payload }));
                    tabs?.postMessage('changed');
                    reconcile();

                    if (payload?.type !== 'inbox_sync') {
                        document.dispatchEvent(new CustomEvent('mayush:toast', {
                            detail: {
                                title: notificationText(payload?.title, 'New notification'),
                                message: notificationText(payload?.message),
                                category: notificationText(payload?.category, 'system'),
                                severity: notificationText(payload?.severity, 'info'),
                            },
                        }));
                        acknowledge(payload?.id);
                    }
                });

            echo.connector.pusher.connection.bind('connected', reconcile);
        } catch {
            // Reverb is an enhancement. The durable inbox continues to reconcile over HTTP.
        }
    };

    initialiseBroadcasting();
}

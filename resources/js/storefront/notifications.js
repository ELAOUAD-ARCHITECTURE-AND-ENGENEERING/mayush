import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const runtime = document.querySelector('meta[name="notification-center"]');

if (runtime) {
    const userId = runtime.dataset.userId;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const summaryUrl = runtime.dataset.summaryUrl;
    const ackTemplate = runtime.dataset.ackUrl;
    const tabs = 'BroadcastChannel' in window ? new BroadcastChannel('mayush-notifications') : null;

    const renderUnreadCount = (count) => {
        document.querySelectorAll('[data-notification-unread-count], .notification-count')
            .forEach((element) => {
                element.textContent = String(count);
                element.hidden = Number(count) === 0;
            });
    };

    const reconcile = async () => {
        try {
            const response = await fetch(summaryUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) return;
            const summary = await response.json();
            renderUnreadCount(summary.unread_count || 0);
            document.dispatchEvent(new CustomEvent('mayush:notifications:summary', { detail: summary }));
        } catch {
            // The persistent inbox remains authoritative and will reconcile later.
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
            // A missing acknowledgement must never affect the inbox record.
        }
    };

    tabs?.addEventListener('message', reconcile);
    document.addEventListener('mayush:notifications:changed', () => {
        tabs?.postMessage('changed');
        reconcile();
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
                await fetch(`/notifications/${encodeURIComponent(link.dataset.notificationId)}/read`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
            } finally {
                window.location.assign(link.href);
            }
        });
    });

    if (runtime.dataset.broadcasting === '1' && runtime.dataset.key && runtime.dataset.host) {
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
                renderUnreadCount(payload.unread_count || 0);
                document.dispatchEvent(new CustomEvent('mayush:notification', { detail: payload }));
                tabs?.postMessage('changed');
                acknowledge(payload.id);
            });

        echo.connector.pusher.connection.bind('connected', reconcile);
    }
}

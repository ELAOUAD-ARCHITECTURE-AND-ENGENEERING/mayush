(function () {
    const appUrlMeta = document.querySelector('meta[name="app-url"]');
    const appUrl = appUrlMeta ? appUrlMeta.content : '';
    const cleanAppUrl = appUrl.endsWith('/') ? appUrl.slice(0, -1) : appUrl;
    const apiUrl = cleanAppUrl + '/api/v2/analytics/track-visit';
    const healthUrl = cleanAppUrl + '/api/v2/analytics/track-health';
    const sessionKey = 'mayush_analytics_sid';

    let sessionId = sessionStorage.getItem(sessionKey);
    if (!sessionId) {
        sessionId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        sessionStorage.setItem(sessionKey, sessionId);
    }

    const startTime = Date.now();
    const isEntryToken = !sessionStorage.getItem('mayush_entry_tracked');

    const getUtmParams = function () {
        const urlParams = new URLSearchParams(window.location.search);
        const utm = {};

        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(function (param) {
            if (urlParams.has(param)) {
                utm[param] = urlParams.get(param);
            }
        });

        return Object.keys(utm).length ? utm : null;
    };

    const sendTrack = function (data) {
        const payload = {
            session_id: sessionId,
            url: window.location.pathname,
            referrer: document.referrer,
            method: 'GET',
            utm: getUtmParams(),
            ...data
        };

        if (data.is_exit && navigator.sendBeacon) {
            navigator.sendBeacon(apiUrl, JSON.stringify(payload));
            return;
        }

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).catch(function () {
            console.debug('Analytics capture suppressed');
        });
    };

    const sendHealth = function (data) {
        fetch(healthUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                source: 'frontend',
                ...data
            })
        }).catch(function () {});
    };

    if (isEntryToken) {
        sendTrack({ is_entry: true });
        sessionStorage.setItem('mayush_entry_tracked', 'true');
    } else {
        sendTrack({});
    }

    window.addEventListener('load', function () {
        setTimeout(function () {
            const nav = performance.getEntriesByType('navigation')[0];
            if (!nav) {
                return;
            }

            sendHealth({
                type: 'latency',
                value: nav.duration,
                unit: 'ms',
                message: 'Page load: ' + window.location.pathname,
                context: {
                    domInteractive: nav.domInteractive,
                    loadEventEnd: nav.loadEventEnd,
                    transferSize: nav.transferSize
                }
            });
        }, 1000);
    });

    window.onerror = function (message, source, lineno, colno, error) {
        sendHealth({
            type: 'error',
            message: message,
            context: {
                source: source,
                line: lineno,
                col: colno,
                stack: error ? error.stack : null,
                url: window.location.href
            }
        });
    };

    window.onunhandledrejection = function (event) {
        sendHealth({
            type: 'error',
            message: 'Unhandled Promise Rejection',
            context: {
                reason: event.reason ? (event.reason.message || event.reason) : 'Unknown',
                stack: event.reason ? event.reason.stack : null,
                url: window.location.href
            }
        });
    };

    const trackDuration = function () {
        const duration = Math.round((Date.now() - startTime) / 1000);
        if (duration > 0) {
            sendTrack({ time_spent: duration });
        }
    };

    window.addEventListener('beforeunload', function () {
        const duration = Math.round((Date.now() - startTime) / 1000);
        sendTrack({ is_exit: true, time_spent: duration });
    });

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            trackDuration();
        }
    });

    document.addEventListener('click', function (event) {
        if (Math.random() > 0.1) {
            return;
        }

        const target = event.target;
        const firstClass = target.className && typeof target.className === 'string'
            ? '.' + target.className.split(' ')[0]
            : '';
        const path = target.tagName + (target.id ? '#' + target.id : '') + firstClass;

        sendTrack({
            click_paths: [{
                x: event.clientX,
                y: event.clientY,
                target: path
            }]
        });
    }, { passive: true });
})();

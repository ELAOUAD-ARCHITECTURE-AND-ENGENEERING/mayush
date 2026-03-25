(function () {
    const API_URL = '/api/v2/analytics/track-visit';
    const HEALTH_URL = '/api/v2/analytics/track-health';
    const SESSION_KEY = 'mayush_analytics_sid';

    // Manage session ID
    let sessionId = sessionStorage.getItem(SESSION_KEY);
    if (!sessionId) {
        sessionId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        sessionStorage.setItem(SESSION_KEY, sessionId);
    }

    const startTime = Date.now();
    let isEntryToken = !sessionStorage.getItem('mayush_entry_tracked');

    // UTM Tracking
    const getUTMParams = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const utm = {};
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(param => {
            if (urlParams.has(param)) utm[param] = urlParams.get(param);
        });
        return Object.keys(utm).length ? utm : null;
    };

    const sendTrack = (data) => {
        const payload = {
            session_id: sessionId,
            url: window.location.pathname,
            referrer: document.referrer,
            method: 'GET',
            utm: getUTMParams(),
            ...data
        };

        // Use beacon for exit tracking, fetch for others
        if (data.is_exit && navigator.sendBeacon) {
            navigator.sendBeacon(API_URL, JSON.stringify(payload));
        } else {
            fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            }).catch(err => console.debug('Analytics capture suppressed'));
        }
    };

    const sendHealth = (data) => {
        fetch(HEALTH_URL, {
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
        }).catch(() => { });
    };

    // Track Entry
    if (isEntryToken) {
        sendTrack({ is_entry: true });
        sessionStorage.setItem('mayush_entry_tracked', 'true');
    } else {
        sendTrack({});
    }

    // Performance Telemetry
    window.addEventListener('load', () => {
        setTimeout(() => {
            const nav = performance.getEntriesByType('navigation')[0];
            if (nav) {
                sendHealth({
                    type: 'latency',
                    value: nav.duration,
                    unit: 'ms',
                    message: `Page load: ${window.location.pathname}`,
                    context: {
                        domInteractive: nav.domInteractive,
                        loadEventEnd: nav.loadEventEnd,
                        transferSize: nav.transferSize
                    }
                });
            }
        }, 1000);
    });

    // Error Tracking
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

    // Track Exit / Time Spent
    const trackDuration = () => {
        const duration = Math.round((Date.now() - startTime) / 1000);
        if (duration > 0) {
            sendTrack({ time_spent: duration });
        }
    };

    window.addEventListener('beforeunload', () => {
        const duration = Math.round((Date.now() - startTime) / 1000);
        sendTrack({ is_exit: true, time_spent: duration });
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            trackDuration();
        }
    });

    // Click Tracking (Sampling)
    document.addEventListener('click', (e) => {
        if (Math.random() > 0.1) return; // Sample 10% of clicks
        const path = e.target.tagName + (e.target.id ? '#' + e.target.id : '') + (e.target.className ? '.' + e.target.className.split(' ')[0] : '');
        sendTrack({ click_paths: [{ x: e.clientX, y: e.clientY, target: path }] });
    }, { passive: true });

})();

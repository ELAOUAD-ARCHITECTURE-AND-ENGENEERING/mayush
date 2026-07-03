(function () {
    'use strict';

    var script = document.currentScript;
    var firstPartyLoaded = false;
    var marketingLoaded = false;
    var whatsappLoaded = false;

    function enabled(name) {
        return script.dataset[name] === '1';
    }

    function loadScript(src, id, onload) {
        if (!src || (id && document.getElementById(id))) {
            return;
        }

        var tag = document.createElement('script');
        tag.async = true;
        tag.src = src;
        if (id) tag.id = id;
        if (onload) tag.onload = onload;
        document.head.appendChild(tag);
    }

    function onIdle(callback, timeout) {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(callback, { timeout: timeout });
        } else {
            window.setTimeout(callback, timeout);
        }
    }

    function loadFirstPartyAnalytics() {
        if (firstPartyLoaded) return;
        firstPartyLoaded = true;
        loadScript(script.dataset.analyticsSrc, 'mayush-analytics-tracker');
    }

    function hasMarketingConsent() {
        return window.localStorage.getItem('mayush_marketing_consent') === 'granted';
    }

    function loadMarketing() {
        if (marketingLoaded || !hasMarketingConsent()) return;
        marketingLoaded = true;

        var gtmId = script.dataset.gtmId || '';
        if (enabled('gtmEnabled') && /^GTM-[A-Z0-9]+$/.test(gtmId)) {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
            loadScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(gtmId), 'mayush-gtm');
        }

        var pixelId = script.dataset.facebookPixelId || '';
        if (enabled('facebookEnabled') && /^\d+$/.test(pixelId)) {
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = true;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = true;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s);
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
            window.fbq('init', pixelId);
            window.fbq('track', 'PageView');
        }
    }

    function loadWhatsapp() {
        if (whatsappLoaded || !enabled('whatsappEnabled') || !script.dataset.whatsappNumber) return;
        whatsappLoaded = true;

        var protocol = document.location.protocol;
        var host = 'getbutton.io';
        loadScript(protocol + '//static.' + host + '/widget-send-button/js/init.js', 'mayush-whatsapp', function () {
            window.WhWidgetSendButton.init(host, protocol, {
                whatsapp: script.dataset.whatsappNumber,
                call_to_action: script.dataset.whatsappLabel || 'Message us',
                position: 'right'
            });
        });
    }

    function applyConsent(value) {
        window.localStorage.setItem('mayush_marketing_consent', value);
        var notice = document.getElementById('marketing-consent-notice');
        if (notice) notice.hidden = true;
        if (value === 'granted') loadMarketing();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector('.cf-turnstile')) {
            loadScript('https://challenges.cloudflare.com/turnstile/v0/api.js', 'mayush-turnstile');
        }

        var notice = document.getElementById('marketing-consent-notice');
        if (notice && !window.localStorage.getItem('mayush_marketing_consent')) notice.hidden = false;

        var accept = document.getElementById('marketing-consent-accept');
        var reject = document.getElementById('marketing-consent-reject');
        if (accept) accept.addEventListener('click', function () { applyConsent('granted'); });
        if (reject) reject.addEventListener('click', function () { applyConsent('denied'); });
    });

    ['pointerdown', 'keydown', 'touchstart'].forEach(function (eventName) {
        window.addEventListener(eventName, function () {
            loadFirstPartyAnalytics();
            loadWhatsapp();
            loadMarketing();
        }, { once: true, passive: true });
    });

    onIdle(loadFirstPartyAnalytics, 1800);
    if (!enabled('deferMarketing')) onIdle(loadMarketing, 2500);
    else if (hasMarketingConsent()) onIdle(loadMarketing, 3500);

    window.mayushGrantMarketingConsent = function () { applyConsent('granted'); };
    window.mayushRejectMarketingConsent = function () { applyConsent('denied'); };
}());

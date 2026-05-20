<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security settings for your Laravel application.
    |
    */

    'monitoring' => [
        'enabled' => true,
        'log_level' => 'warning',
        'rate_limit' => [
            'max_requests' => 60,
            'time_window' => 60, // seconds
        ],
        'failed_login' => [
            'max_attempts' => 5,
            'lockout_time' => 900, // 15 minutes
        ],
        'block_duration' => 900, // 15 minutes
    ],

    'headers' => [
        'hsts' => [
            'enabled' => true,
            'max_age' => 31536000, // 1 year
            'include_subdomains' => true,
            'preload' => true,
        ],
        'csp' => [
            'enabled' => true,
            'default_src' => ["'self'"],
            'script_src' => [
                "'self'",
                "'unsafe-inline'",
                "'unsafe-eval'",
                "*.google.com",
                "*.googleapis.com",
                "*.googletagmanager.com",
                "*.gstatic.com",
                "*.google-analytics.com",
                "*.googleadservices.com",
                "*.doubleclick.net",
                "*.cmi.co.ma",
                "*.paypal.com",
                "*.stripe.com",
                "*.razorpay.com",
                "*.paystack.co",
                "*.flutterwave.com",
                "*.paytm.com",
                "*.instamojo.com",
                "*.payumoney.com",
                "*.ccavenue.com",
                "*.billdesk.com",
                "https://static.cloudflareinsights.com",
                "https://connect.facebook.net",
                "*.facebook.net",
                "*.facebook.com",
            ],
            'style_src' => [
                "'self'",
                "'unsafe-inline'",
                "*.googleapis.com",
                "*.gstatic.com",
                "fonts.googleapis.com",
                "use.fontawesome.com",
                "cdnjs.cloudflare.com",
                "maxcdn.bootstrapcdn.com",
            ],
            'img_src' => [
                "'self'",
                "data:",
                "blob:",
                "*.gravatar.com",
                "*.google.com",
                "*.googleapis.com",
                "*.gstatic.com",
                "*.google-analytics.com",
                "*.googleadservices.com",
                "*.doubleclick.net",
                "*.googletagmanager.com",
                "*.gravatar.com",
                "*.paypal.com",
                "*.stripe.com",
                "*.razorpay.com",
                "*.paystack.co",
                "*.flutterwave.com",
                "*.paytm.com",
                "*.instamojo.com",
                "*.payumoney.com",
                "*.ccavenue.com",
                "*.billdesk.com",
            ],
            'font_src' => [
                "'self'",
                "data:",
                "*.googleapis.com",
                "*.gstatic.com",
                "fonts.gstatic.com",
                "fonts.googleapis.com",
                "use.fontawesome.com",
                "cdnjs.cloudflare.com",
                "maxcdn.bootstrapcdn.com",
            ],
            'connect_src' => [
                "'self'",
                "*.cmi.co.ma",
                "*.google.com",
                "*.googleapis.com",
                "*.gstatic.com",
                "*.google-analytics.com",
                "*.googleadservices.com",
                "*.doubleclick.net",
                "*.googletagmanager.com",
                "https://cloudflareinsights.com",
                "*.paypal.com",
                "*.stripe.com",
                "*.razorpay.com",
                "*.paystack.co",
                "*.flutterwave.com",
                "*.paytm.com",
                "*.instamojo.com",
                "*.payumoney.com",
                "*.ccavenue.com",
                "*.billdesk.com",
                "*.activeitzone.com",
            ],
            'media_src' => ["'self'"],
            'object_src' => ["'none'"],
            'frame_src' => [
                "'self'",
                "*.cmi.co.ma",
                "*.paypal.com",
                "*.stripe.com",
                "*.razorpay.com",
                "*.paystack.co",
                "*.flutterwave.com",
                "*.paytm.com",
                "*.instamojo.com",
                "*.payumoney.com",
                "*.ccavenue.com",
                "*.billdesk.com",
            ],
            'frame_ancestors' => ["'self'"],
            'base_uri' => ["'self'"],
            'form_action' => [
                "'self'",
                // CMI Payment Gateway
                "https://testpayment.cmi.co.ma",
                "https://test-attijari.cmi.co.ma",
                "https://attijari-payment.cmi.co.ma",
                "https://payment.cmi.co.ma",
                // PayPal
                "https://www.paypal.com",
                "https://www.sandbox.paypal.com",
                // Stripe
                "https://api.stripe.com",
                "https://checkout.stripe.com",
                // Razorpay
                "https://api.razorpay.com",
                "https://checkout.razorpay.com",
                // Paystack
                "https://api.paystack.co",
                "https://standard.paystack.co",
                // Flutterwave
                "https://api.flutterwave.com",
                "https://checkout.flutterwave.com",
                // Other common payment gateways
                "https://*.paytm.com",
                "https://*.instamojo.com",
                "https://*.payumoney.com",
                "https://*.ccavenue.com",
                "https://*.billdesk.com",
            ],
        ],
        'x_frame_options' => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    'ssl' => [
        'force_https' => env('FORCE_HTTPS', false),
        'secure_cookies' => env('SECURE_COOKIES', false),
    ],

    'admin' => [
        'session_timeout' => 1800, // 30 minutes
        'password_expiry' => 90, // days
        'max_sessions' => 3,
    ],

    'logging' => [
        'enabled' => true,
        'log_admin_activities' => true,
        'log_failed_logins' => true,
        'log_suspicious_activity' => true,
        'retention_days' => 30,
    ],

    'notifications' => [
        'security_alerts' => [
            'enabled' => true,
            'email' => env('SECURITY_ALERT_EMAIL', env('ADMIN_EMAIL')),
            'threshold' => 'warning',
        ],
    ],
];

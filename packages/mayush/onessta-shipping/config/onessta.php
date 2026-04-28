<?php

return [
    'enabled' => env('ONESSTA_ENABLED', false),

    'mode' => env('ONESSTA_MODE', 'live'),

    'base_url' => env('ONESSTA_BASE_URL', 'https://api.onessta.com/api/v1'),

    'auth' => [
        'token' => env('ONESSTA_TOKEN'),
        'api_key' => env('ONESSTA_API_KEY'),
        'client_id' => env('ONESSTA_CLIENT_ID'),
    ],

    'http' => [
        'timeout' => (int) env('ONESSTA_TIMEOUT', 30),
        'connect_timeout' => (int) env('ONESSTA_CONNECT_TIMEOUT', 10),
        'retry_times' => (int) env('ONESSTA_RETRY_TIMES', 3),
        'retry_sleep_ms' => (int) env('ONESSTA_RETRY_SLEEP_MS', 500),
        'retry_codes' => [408, 502, 503, 504],
    ],

    'queue' => [
        'connection' => env('ONESSTA_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'create_shipment_connection' => env('ONESSTA_CREATE_SHIPMENT_QUEUE_CONNECTION', 'sync'),
        'name' => env('ONESSTA_QUEUE_NAME', 'onessta'),
        'create_shipment_retry' => [60, 300, 900],
        'poll_tracking_retry' => [30, 120, 300],
        'webhook_process_retry' => [10, 30, 60],
    ],

    'cache' => [
        'store' => env('ONESSTA_CACHE_STORE', 'redis'),
        'ttl_cities' => (int) env('ONESSTA_CACHE_TTL_CITIES', 86400),
        'ttl_pickup_cities' => (int) env('ONESSTA_CACHE_TTL_PICKUP_CITIES', 86400),
        'ttl_tracking' => (int) env('ONESSTA_CACHE_TTL_TRACKING', 300),
        'ttl_capabilities' => (int) env('ONESSTA_CACHE_TTL_CAPABILITIES', 999999999),
    ],

    'webhook' => [
        'enabled' => env('ONESSTA_WEBHOOK_ENABLED', true),
        'api_key' => env('ONESSTA_WEBHOOK_API_KEY'),
        'secret' => env('ONESSTA_WEBHOOK_SECRET'),
        'route' => env('ONESSTA_WEBHOOK_ROUTE', '/webhooks/onessta'),
        'queue' => (bool) env('ONESSTA_WEBHOOK_QUEUE', true),
        'fail_on_signature_mismatch' => (bool) env('ONESSTA_FAIL_ON_SIGNATURE_MISMATCH', true),
    ],

    'capabilities' => [
        'quotes' => (bool) env('ONESSTA_SUPPORT_QUOTES', false),
        'labels' => (bool) env('ONESSTA_SUPPORT_LABELS', false),
        'products' => (bool) env('ONESSTA_SUPPORT_PRODUCTS', true),
        'stock' => (bool) env('ONESSTA_SUPPORT_STOCK', true),
    ],

    'polling' => [
        'enabled' => (bool) env('ONESSTA_POLLING_ENABLED', true),
        'interval_minutes' => (int) env('ONESSTA_POLLING_INTERVAL_MINUTES', 5),
        'max_parcels_per_run' => (int) env('ONESSTA_POLLING_MAX_PARCELS', 100),
    ],

    'throttle' => [
        'tracking_per_parcel_per_min' => 1,
        'cities_refresh_per_day' => 1,
        'pickup_cities_refresh_per_day' => 1,
    ],

    'log_channel' => env('ONESSTA_LOG_CHANNEL', 'stack'),
];

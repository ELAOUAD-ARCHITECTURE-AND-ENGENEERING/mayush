<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Structured Data Defaults
    |--------------------------------------------------------------------------
    |
    | These defaults are used only when product/category settings do not provide
    | a more specific value. Keep them conservative and aligned with live policy.
    |
    */
    'return_policy_days' => (int) env('SEO_RETURN_POLICY_DAYS', 15),
    'shipping_default_days' => (int) env('SEO_SHIPPING_DEFAULT_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | IndexNow
    |--------------------------------------------------------------------------
    |
    | Set INDEXNOW_KEY to enable manual URL submission to Bing/Copilot-backed
    | discovery systems. The service no-ops safely when the key is missing.
    |
    */
    'indexnow' => [
        'enabled' => (bool) env('INDEXNOW_ENABLED', false),
        'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),
        'key' => env('INDEXNOW_KEY'),
        'key_location' => env('INDEXNOW_KEY_LOCATION'),
    ],
];

<?php

return [
    'update' => [
        'latest_version' => env('MOBILE_LATEST_VERSION', '1.0.0'),
        'minimum_version' => env('MOBILE_MINIMUM_VERSION', '1.0.0'),
        'latest_runtime_version' => env('MOBILE_LATEST_RUNTIME_VERSION', '1.0.0'),
        'published_at' => env('MOBILE_UPDATE_PUBLISHED_AT'),
        'store_urls' => [
            'android' => env('MOBILE_ANDROID_STORE_URL'),
            'ios' => env('MOBILE_IOS_STORE_URL'),
        ],
        'release_notes' => [
            'fr' => array_values(array_filter(explode('|', (string) env('MOBILE_RELEASE_NOTES_FR', '')))),
            'ar' => array_values(array_filter(explode('|', (string) env('MOBILE_RELEASE_NOTES_AR', '')))),
        ],
    ],

    'maintenance' => [
        'active' => filter_var(env('MOBILE_MAINTENANCE_ACTIVE', false), FILTER_VALIDATE_BOOL),
        'starts_at' => env('MOBILE_MAINTENANCE_STARTS_AT'),
        'ends_at' => env('MOBILE_MAINTENANCE_ENDS_AT'),
        'support_url' => env('MOBILE_MAINTENANCE_SUPPORT_URL'),
        'updated_at' => env('MOBILE_MAINTENANCE_UPDATED_AT'),
        'title' => [
            'fr' => env('MOBILE_MAINTENANCE_TITLE_FR', 'Maintenance en cours'),
            'ar' => env('MOBILE_MAINTENANCE_TITLE_AR', 'الصيانة جارية'),
        ],
        'message' => [
            'fr' => env('MOBILE_MAINTENANCE_MESSAGE_FR', 'Mayush est temporairement indisponible. Veuillez réessayer dans quelques instants.'),
            'ar' => env('MOBILE_MAINTENANCE_MESSAGE_AR', 'مايوش غير متاح مؤقتاً. يرجى المحاولة بعد قليل.'),
        ],
    ],

    'service' => [
        'available' => filter_var(env('MOBILE_SERVICE_AVAILABLE', true), FILTER_VALIDATE_BOOL),
        'retry_after_seconds' => (int) env('MOBILE_SERVICE_RETRY_AFTER_SECONDS', 60),
    ],
];

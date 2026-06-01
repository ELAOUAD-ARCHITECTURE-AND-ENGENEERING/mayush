<?php

return [
    'disk' => env('IMAGE_OPTIMIZATION_DISK', config('filesystems.default')),
    'static_disk' => env('IMAGE_OPTIMIZATION_STATIC_DISK', 'local'),
    'queue' => env('IMAGE_OPTIMIZATION_QUEUE', 'images'),
    'quality' => (int) env('IMAGE_OPTIMIZATION_QUALITY', 80),
    'max_width' => (int) env('IMAGE_OPTIMIZATION_MAX_WIDTH', 1500),
    'recipe_version' => env('IMAGE_OPTIMIZATION_RECIPE_VERSION', '1'),
    'audit_limit' => (int) env('IMAGE_OPTIMIZATION_AUDIT_LIMIT', 500),
    'variants' => [
        'small' => 160,
        'thumb' => 300,
        'medium' => 600,
        'large' => 1200,
    ],
    'static_assets' => [
        'assets/img/office_furniture_4k.png',
        'assets/img/cards/verified_by_visa.png',
        'assets/img/cards/secure_code.png',
        'assets/img/cards/amex.png',
        'assets/img/cards/cmi.png',
        'assets/img/cards/marocpay.png',
        'assets/img/cards/unionpay.png',
    ],
];

<?php

return [
    'asset_profiles_enabled' => env('STOREFRONT_ASSET_PROFILE_ENABLED', true),
    'defer_marketing' => env('STOREFRONT_DEFER_MARKETING', true),
    'fragment_cache' => env('STOREFRONT_FRAGMENT_CACHE', true),
    'server_timing' => env('STOREFRONT_SERVER_TIMING', false),
    'gtm_id' => env('GTM_ID', 'GTM-KSHLDCWK'),
    'tracking_id' => env('TRACKING_ID'),
    'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID'),
];

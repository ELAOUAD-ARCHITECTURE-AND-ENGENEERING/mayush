<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mock Carrier Guard
    |--------------------------------------------------------------------------
    |
    | Mock tracking is useful for local development and tests, but production
    | must not silently present fake carrier updates as real logistics data.
    |
    */
    'mock_carrier_enabled' => env('LOGISTICS_MOCK_CARRIER_ENABLED', false),
];

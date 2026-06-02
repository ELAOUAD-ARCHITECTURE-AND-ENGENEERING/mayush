<?php

return [
    'merchant_id' => env('CMI_MERCHANT_ID', env('CMI_CLIENT_ID')),
    'secret_key' => env('CMI_SECRET_KEY', env('CMI_STORE_KEY')),
    'gateway_url' => env('CMI_GATEWAY_URL', 'https://testpayment.cmi.co.ma/fim/est3Dgate'),
    'ok_url' => env('CMI_OK_URL'),
    'fail_url' => env('CMI_FAIL_URL'),
    'callback_url' => env('CMI_CALLBACK_URL'),
    'store_type' => env('CMI_STORE_TYPE', '3D_PAY_HOSTING'),
    'demo_mode' => env('DEMO_MODE', 'Off'),
    
    // IP Whitelist for CMI callback (empty array means disabled, though recommended in production)
    'allowed_ips' => explode(',', env('CMI_ALLOWED_IPS', '')),
];

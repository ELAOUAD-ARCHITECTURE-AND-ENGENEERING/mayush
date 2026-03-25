<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$requests = [
    '/all-notifications',
    '/admin/contacts',
    '/admin/shipping_configuration',
    '/admin/pickup_address',
    '/admin/shipping_box_size',
    '/'
];

foreach ($requests as $uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $response = $kernel->handle($request);
    echo "GET $uri -> " . $response->getStatusCode() . "\n";
}

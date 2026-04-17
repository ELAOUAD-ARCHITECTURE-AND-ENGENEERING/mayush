<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$client = new \Mayush\Shipping\Onessta\Client\OnesstaClient(
    config('onessta.base_url'),
    (string) config('onessta.auth.token'),
    (string) config('onessta.auth.api_key'),
    (string) config('onessta.auth.client_id')
);

try {
    $response = $client->post('/p/parcels/add', [
        'code' => 'TEST-0001',
        'receiver' => 'Ahmed Test',
        'phone' => '+212600000000',
        'price' => '100',
        'city' => ['id' => 101],
        'address' => 'Casablanca',
        'can_open' => false,
        'replace' => false
    ]);
    echo "Success: \n";
    print_r($response->json());
} catch (\Throwable $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}

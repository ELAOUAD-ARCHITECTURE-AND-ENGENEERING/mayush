<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$email = 'seller_debug_' . uniqid() . '@example.com';
$data = [
    'name' => 'John Doe',
    'email' => $email,
    'password' => 'password',
    'password_confirmation' => 'password',
    'shop_name' => 'My New Shop',
    'address' => '123 Fake Street',
    'phone' => '1234567890'
];

echo "Testing Seller Registration with email: $email\n";

try {
    $request = Illuminate\Http\Request::create('/shops', 'POST', $data);
    // Manually set session and other things if needed, but Kernel should handle it
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 500) {
        echo "ERROR 500 DETECTED!\n";
        echo substr($response->getContent(), 0, 2000) . "\n";
    } else {
        echo "Success (or at least not 500)\n";
    }
} catch (\Exception $e) {
    echo "CRASH: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

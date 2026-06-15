<?php

use Illuminate\Support\Facades\Http;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$payload = '" onfocus="alert(1)" autofocus="';
$url = 'http://localhost/search?q=' . urlencode($payload);

echo "Testing URL: $url\n";

try {
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::create($url, 'GET')
    );
    
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 500) {
        echo "ERROR 500 DETECTED!\n";
        // In a real Laravel app, we might need to check logs or the response content if debugging is on
        echo substr($response->getContent(), 0, 1000) . "\n";
    } else {
        echo "Success (or at least not 500)\n";
    }
} catch (\Exception $e) {
    echo "CRASH: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/', 'GET');
// Manually set the request in the container if needed
$app->instance('request', $request);

try {
    $response = $kernel->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() != 200) {
        // echo "Content: " . substr($response->getContent(), 0, 1000) . "\n";
    }
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . substr($e->getTraceAsString(), 0, 1000) . "\n";
}

$kernel->terminate($request, $response ?? null);


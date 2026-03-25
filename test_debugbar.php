<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/_debugbar/open?op=get&id=01KK9G42S4MV4ZGHCKZV4QYHH4', 'GET');
try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 500) {
        if ($response->exception) {
            echo "Exception: " . $response->exception->getMessage() . "\n" . $response->exception->getTraceAsString();
        } else {
            echo "Response: " . substr($response->getContent(), 0, 1000);
        }
    }
} catch (\Exception $e) {
    echo "Caught: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

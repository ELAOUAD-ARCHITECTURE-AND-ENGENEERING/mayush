<?php

$request = \Illuminate\Http\Request::create('/livechat/initiate', 'POST');
$controller = app()->make(\App\Http\Controllers\Support\LiveChatController::class);

try {
    $response = $controller->initiate($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

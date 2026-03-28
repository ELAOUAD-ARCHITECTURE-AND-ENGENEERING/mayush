<?php
// Direct HTTP simulation - call via the real Request/Router pipeline
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create(
    '/mayush/admin/analytics/currency-config',
    'GET',
    [],
    [],
    [],
    ['HTTP_HOST' => 'localhost']
);

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
$body = $response->getContent();
// Try to show error
if ($response->getStatusCode() >= 400) {
    // Check if JSON error
    $json = json_decode($body, true);
    if ($json && isset($json['message'])) {
        echo "Error: " . $json['message'] . "\n";
        if (isset($json['trace'])) {
            foreach (array_slice($json['trace'], 0, 5) as $t) {
                echo "  at " . ($t['file'] ?? '') . ":" . ($t['line'] ?? '') . "\n";
            }
        }
    } else {
        // Show first 2000 chars of HTML error
        echo substr(strip_tags($body), 0, 2000) . "\n";
    }
} else {
    echo "OK: " . substr($body, 0, 500) . "\n";
}

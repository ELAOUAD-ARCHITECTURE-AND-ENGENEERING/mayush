<?php
// Simple test script for analytics endpoints
$endpoints = [
    'visitor-stats',
    'vendor-analytics',
    'finance-analytics',
    'marketing-analytics'
];

$baseUrl = 'http://localhost/mayush/admin/analytics/';

foreach ($endpoints as $e) {
    echo "Testing $e...\n";
    // We would normally need auth, but here we can just check if the logic runs without 500
    // manually for now or use artisan tinker
    echo "Run: php artisan tinker --execute=\"echo json_encode(app(App\\Http\\Controllers\\Api\\AnalyticsController::class)->get" . str_replace('-', '', ucwords($e, '-')) . "(request()))\"\n";
}

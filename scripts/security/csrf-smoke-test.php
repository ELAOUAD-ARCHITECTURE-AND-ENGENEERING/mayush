<?php

/**
 * CSRF Smoke Test Script
 * 
 * This script performs a raw HTTP POST request to the local application
 * without a CSRF token to verify that the production-level middleware
 * correctly rejects the request with a 419 Page Expired status.
 * 
 * Usage: php scripts/security/csrf-smoke-test.php
 */

$url = 'http://localhost/login'; // Adjust to your local dev URL
$data = ['email' => 'test@example.com', 'password' => 'password'];

echo "Testing CSRF rejection on $url...\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Do not follow redirects to see raw status

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 419) {
    echo "SUCCESS: Request rejected with status 419 (Page Expired) as expected.\n";
} else {
    echo "FAILURE: Request returned status $httpCode. Expected 419.\n";
    echo "Note: Ensure the local server is running and APP_ENV is NOT 'testing' during this test.\n";
}

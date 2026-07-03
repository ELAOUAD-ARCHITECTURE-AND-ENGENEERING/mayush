<?php

$url = $argv[1] ?? 'http://localhost/login';
$payload = http_build_query([
    'email' => 'csrf-smoke@example.com',
    'password' => 'invalid-password',
]);

echo "Testing CSRF rejection on {$url}...\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 15,
]);

$body = curl_exec($ch);
$error = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    fwrite(STDERR, "Request failed: {$error}\n");
    exit(2);
}

if ($status === 419) {
    echo "SUCCESS: request rejected with HTTP 419.\n";
    exit(0);
}

echo "FAILURE: expected HTTP 419, received HTTP {$status}.\n";
if ($body) {
    echo substr($body, 0, 500)."\n";
}
exit(1);

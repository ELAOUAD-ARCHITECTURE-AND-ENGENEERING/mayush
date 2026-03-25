<?php
// Make a real HTTP request via CURL to see the actual error
$url = 'https://localhost/mayush/admin/analytics/currency-config';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $code\n";
// Strip HTML and show text
$text = strip_tags($body);
// Remove excess whitespace
$text = preg_replace('/\s+/', ' ', $text);
echo substr($text, 0, 3000) . "\n";

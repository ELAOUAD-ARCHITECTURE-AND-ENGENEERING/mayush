<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$productionBase = 'https://mayushdesign.com/public/';
$out = "";

// Get a sample of missing files
$allUploads = \App\Models\Upload::whereNull('external_link')->get(['id','file_name']);
$missing = [];

foreach($allUploads as $u) {
    $path = public_path($u->file_name);
    if (!file_exists($path)) {
        $basename = basename($u->file_name);
        if (!file_exists(public_path('uploads/all/' . $basename))) {
            $missing[] = $u;
            if (count($missing) >= 5) break;
        }
    }
}

$out .= "Testing 5 missing files against production:\n\n";
foreach($missing as $u) {
    $url = $productionBase . $u->file_name;
    $out .= "  ID={$u->id}: {$url}\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);
    
    $out .= "    HTTP {$httpCode} | Type: {$contentType} | Size: {$size}\n\n";
}

file_put_contents(__DIR__ . '/production_check.txt', $out);
echo $out;

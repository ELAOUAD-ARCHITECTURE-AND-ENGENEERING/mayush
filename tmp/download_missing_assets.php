<?php
/**
 * Download missing image assets from production server
 * 
 * Iterates all local Upload records, checks if file exists on disk,
 * and downloads missing files from https://mayushdesign.com/public/
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$productionBase = 'https://mayushdesign.com/public/';
$targetDir = public_path('uploads/all');

// Ensure target directory exists
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Get all local uploads
$allUploads = \App\Models\Upload::whereNull('external_link')->get(['id', 'file_name']);

$total = count($allUploads);
$alreadyExists = 0;
$downloaded = 0;
$failed = 0;
$failures = [];

echo "=== DOWNLOADING MISSING ASSETS ===\n";
echo "Total local upload records: {$total}\n";
echo "Production base: {$productionBase}\n\n";

foreach ($allUploads as $i => $upload) {
    $fileName = $upload->file_name;
    $localPath = public_path($fileName);
    $basename = basename($fileName);
    $fallbackPath = public_path('uploads/all/' . $basename);

    // Skip if file already exists
    if (file_exists($localPath) || file_exists($fallbackPath)) {
        $alreadyExists++;
        continue;
    }

    // Download from production
    $url = $productionBase . $fileName;
    $savePath = $fallbackPath; // Save to uploads/all/ directory

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentLength = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    curl_close($ch);

    if ($httpCode === 200 && $data !== false && $contentLength > 0) {
        file_put_contents($savePath, $data);
        $downloaded++;
        
        // Progress update every 50 files
        if ($downloaded % 50 === 0) {
            echo "  Downloaded: {$downloaded} | Failed: {$failed} | Checked: " . ($i + 1) . "/{$total}\n";
        }
    } else {
        $failed++;
        if (count($failures) < 20) {
            $failures[] = "[{$upload->id}] HTTP {$httpCode} | {$url}";
        }
    }
}

echo "\n=== RESULTS ===\n";
echo "Already existed: {$alreadyExists}\n";
echo "Downloaded:      {$downloaded}\n";
echo "Failed:          {$failed}\n";
echo "Total processed: {$total}\n";

if (!empty($failures)) {
    echo "\nFailed downloads (first 20):\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
}

// Write results to file
$results = "Download completed at " . date('Y-m-d H:i:s') . "\n";
$results .= "Already existed: {$alreadyExists}\n";
$results .= "Downloaded: {$downloaded}\n";
$results .= "Failed: {$failed}\n";
$results .= "Total: {$total}\n";
if (!empty($failures)) {
    $results .= "\nFailures:\n" . implode("\n", $failures) . "\n";
}
file_put_contents(__DIR__ . '/download_results.txt', $results);

echo "\nResults saved to tmp/download_results.txt\n";

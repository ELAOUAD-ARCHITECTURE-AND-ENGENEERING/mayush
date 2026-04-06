<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Upload;
use Illuminate\Support\Facades\File;

echo "Starting Database Image Path Synchronization..." . PHP_EOL;

$uploads = Upload::where('type', 'image')->get();
$total = count($uploads);
$updated = 0;
$missing = 0;
$already_correct = 0;

foreach ($uploads as $index => $upload) {
    $current_path = $upload->file_name;
    $full_path = public_path($current_path);

    if (File::exists($full_path)) {
        $already_correct++;
        continue;
    }

    // Try finding by basename in uploads/all/
    $basename = basename($current_path);
    $new_relative_path = 'uploads/all/' . $basename;
    $new_full_path = public_path($new_relative_path);

    if (File::exists($new_full_path)) {
        $upload->file_name = $new_relative_path;
        $upload->save();
        $updated++;
        echo "FIXED: {$current_path} -> {$new_relative_path}" . PHP_EOL;
    } else {
        $missing++;
        // echo "STILL MISSING: {$current_path}" . PHP_EOL;
    }
}

echo PHP_EOL;
echo "Sync Complete!" . PHP_EOL;
echo "Total Records Processed: {$total}" . PHP_EOL;
echo "Already Correct: {$already_correct}" . PHP_EOL;
echo "Updated to 'uploads/all/': {$updated}" . PHP_EOL;
echo "Still Missing on Disk: {$missing}" . PHP_EOL;

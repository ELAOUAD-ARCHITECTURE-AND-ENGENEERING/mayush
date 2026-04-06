<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$u = \App\Models\Upload::where('type', 'image')->first();
if ($u) {
    echo "DB path: " . $u->file_name . PHP_EOL;
    echo "Public path: " . public_path($u->file_name) . PHP_EOL;
    echo "File exists: " . (file_exists(public_path($u->file_name)) ? "YES" : "NO") . PHP_EOL;
    
    // Suggest fix if missing
    if (!file_exists(public_path($u->file_name))) {
        $basename = basename($u->file_name);
        $test_path = public_path('uploads/all/' . $basename);
        echo "Testing with 'uploads/all/' prefix: " . $test_path . " | Exists: " . (file_exists($test_path) ? "YES" : "NO") . PHP_EOL;
    }
} else {
    echo "No image found in DB" . PHP_EOL;
}

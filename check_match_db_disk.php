<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$upload = \App\Models\Upload::first();
if (!$upload) {
    die("No uploads found in DB.\n");
}

$file_name = $upload->file_name;
$full_path = public_path($file_name);

echo "DB file_name value: [" . $file_name . "]\n";
echo "Expected physical path: [" . $full_path . "]\n";

if (file_exists($full_path)) {
    echo "Check 1 (exact): File EXISTS on disk!\n";
} else {
    echo "Check 1 (exact): File DOES NOT exist on disk.\n";
    // Check for case mismatch or hidden characters
    $dir = dirname($full_path);
    if (is_dir($dir)) {
        $files = scandir($dir);
        $found = false;
        foreach ($files as $f) {
            if (trim(strtolower($f)) === trim(strtolower(basename($file_name)))) {
                echo "Found case-insensitive/trimmed match: [" . $f . "]\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "No match found in directory: " . $dir . "\n";
        }
    } else {
        echo "Directory NOT found: " . $dir . "\n";
    }
}
?>

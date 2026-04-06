<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "public_path(): " . public_path() . "\n";
$file = 'uploads/all/2xIuWE2zjNjq9HMaiAStzp41zRkfQm6Y9zuY9Asn.webp';
$full_path = public_path($file);
echo "Full path: " . $full_path . "\n";

if (file_exists($full_path)) {
    echo "File exists on disk!\n";
} else {
    echo "File DOES NOT exist on disk.\n";
    // Check if it's in a subdirectory or something
    $dir = public_path('uploads/all');
    if (is_dir($dir)) {
        echo "Directory exists: " . $dir . "\n";
        $files = glob($dir . '/*');
        echo "Files count in directory: " . count($files) . "\n";
        if (count($files) > 0) {
            echo "First file: " . $files[0] . "\n";
        }
    } else {
        echo "Directory DOES NOT exist: " . $dir . "\n";
    }
}

// Check uploads table for the first 5 entries again
$uploads = \App\Models\Upload::take(5)->get();
foreach ($uploads as $u) {
    echo "Upload ID: " . $u->id . " | file_name: " . $u->file_name . "\n";
}
?>

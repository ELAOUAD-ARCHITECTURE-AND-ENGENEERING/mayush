<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Upload;
use App\Models\BusinessSetting;

// Find the last 2 uploads which were my AI generated ones
$uploads = Upload::orderBy('id', 'desc')->take(2)->get();
if ($uploads->count() == 2) {
    // Because orderBy desc gets the latest first, the second banner is first.
    // Let's sort them by id asc to keep banner 1 and banner 2 in order
    $uploads = $uploads->sortBy('id')->values();
    
    $images = [$uploads[0]->id, $uploads[1]->id];
    
    BusinessSetting::updateOrCreate(['type' => 'home_banner1_images'], ['value' => json_encode($images)]);
    echo "Fixed home_banner1_images with new IDs: " . json_encode($images) . "\n";
} else {
    echo "Could not find the 2 uploads\n";
}

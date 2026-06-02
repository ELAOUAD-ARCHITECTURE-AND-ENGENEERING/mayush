<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\BusinessSetting;
use App\Models\Upload;

echo "home_banner1_images: " . BusinessSetting::where('type', 'home_banner1_images')->value('value') . "\n";

$images = json_decode(BusinessSetting::where('type', 'home_banner1_images')->value('value'), true);
if (is_array($images)) {
    foreach ($images as $id) {
        $upload = Upload::find($id);
        echo "Upload ID $id file_name: " . ($upload ? $upload->file_name : 'NOT FOUND') . "\n";
    }
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\BusinessSetting;

$images = json_decode(BusinessSetting::where('type', 'home_banner4_images')->first()->value);
$titles = json_decode(BusinessSetting::where('type', 'home_banner4_titles')->first()->value);
$descriptions = json_decode(BusinessSetting::where('type', 'home_banner4_descriptions')->first()->value);
$ctas = json_decode(BusinessSetting::where('type', 'home_banner4_cta_texts')->first()->value);
$links = json_decode(BusinessSetting::where('type', 'home_banner4_links')->first()->value);

BusinessSetting::updateOrCreate(['type' => 'home_banner1_images'], ['value' => json_encode($images)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner1_titles'], ['value' => json_encode($titles)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner1_descriptions'], ['value' => json_encode($descriptions)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner1_cta_texts'], ['value' => json_encode($ctas)]);
BusinessSetting::updateOrCreate(['type' => 'home_banner1_links'], ['value' => json_encode($links)]);

echo "Successfully copied to banner 1\n";

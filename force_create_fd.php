<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('db');

use App\Models\FlashDeal;

$fd = new FlashDeal;
$fd->title = 'Test Deal 2026';
$fd->slug = 'test-deal-2026';
$fd->status = 1;
$fd->featured = 1;
$fd->start_date = time() - 3600;
$fd->end_date = time() + 86400;
$fd->date_range = date('d-m-Y H:i:s', $fd->start_date) . ' to ' . date('d-m-Y H:i:s', $fd->end_date);

if($fd->save()){
    echo "Flash Deal Created Successfully! ID: " . $fd->id . "\n";
} else {
    echo "Failed to create Flash Deal\n";
}

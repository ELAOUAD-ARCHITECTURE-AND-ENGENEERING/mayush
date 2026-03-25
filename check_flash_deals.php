<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('db');

use App\Models\FlashDeal;

$today = strtotime(date('Y-m-d H:i:s'));
echo "Today: " . date('Y-m-d H:i:s', $today) . " ($today)\n\n";

$fds = FlashDeal::all();
foreach ($fds as $fd) {
    echo "ID: " . $fd->id . "\n";
    echo "Title: " . $fd->title . "\n";
    echo "Status: " . $fd->status . "\n";
    echo "Start: " . date('Y-m-d H:i:s', $fd->start_date) . " (" . $fd->start_date . ")\n";
    echo "End: " . date('Y-m-d H:i:s', $fd->end_date) . " (" . $fd->end_date . ")\n";
    
    if ($fd->status == 1 && $fd->start_date <= $today && $fd->end_date > $today) {
        echo "RESULT: ACTIVE AND VISIBLE\n";
    } else {
        echo "RESULT: HIDDEN (";
        if ($fd->status != 1) echo "Inactive ";
        if ($fd->start_date > $today) echo "Not started ";
        if ($fd->end_date <= $today) echo "Expired ";
        echo ")\n";
    }
    echo "---------------------------\n";
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$uploads = \App\Models\Upload::take(5)->get();
foreach ($uploads as $u) {
    echo "ID: " . $u->id . " | File: " . $u->file_name . " | Ext: " . $u->external_link . "\n";
}
?>

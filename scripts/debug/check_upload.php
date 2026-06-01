<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Upload 5074 filename: " . \App\Models\Upload::find(5074)->file_name . "\n";
echo "Upload 5075 filename: " . \App\Models\Upload::find(5075)->file_name . "\n";

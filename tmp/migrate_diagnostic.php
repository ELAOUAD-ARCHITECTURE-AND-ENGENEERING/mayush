<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Attempting to create product_views table...\n";
    Schema::create('product_views', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id')->index();
        $table->unsignedBigInteger('user_id')->nullable()->index();
        $table->string('ip_address')->nullable();
        $table->string('session_id')->nullable()->index();
        $table->timestamp('created_at')->nullable()->index();
        $table->timestamp('updated_at')->nullable();

        // Foreign key disabled for now as per previous attempt
        // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
    echo "Table created successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "STACK TRACE:\n" . $e->getTraceAsString() . "\n";
}

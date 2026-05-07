<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_product_translations')) {
            Schema::create('customer_product_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('customer_product_id')->index();
                $table->string('name', 255)->nullable();
                $table->string('unit', 200)->nullable();
                $table->mediumText('description')->nullable();
                $table->string('lang', 10)->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_product_translations');
    }
};

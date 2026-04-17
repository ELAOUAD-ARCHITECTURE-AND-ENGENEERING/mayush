<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('onessta_city_maps')) {
            Schema::create('onessta_city_maps', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('remote_city_id')->unique();
                $table->string('remote_city_name');
                $table->unsignedBigInteger('local_city_id')->nullable()->index();
                $table->string('local_city_name')->nullable();
                $table->boolean('is_pickup')->default(false);
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onessta_city_maps');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('searches')) {
            Schema::create('searches', function (Blueprint $table) {
                $table->id();
                $table->string('query')->unique();
                $table->unsignedInteger('count')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('searches');
    }
};

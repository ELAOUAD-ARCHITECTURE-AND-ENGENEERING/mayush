<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitor_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->index();
            $table->string('method', 10)->default('GET');
            $table->string('referrer')->nullable();
            
            // Geolocation Data
            $table->string('country_code', 5)->nullable()->index();
            $table->string('city')->nullable();
            
            // Behavioral Metrics
            $table->boolean('is_entry')->default(false);
            $table->boolean('is_exit')->default(false);
            $table->integer('time_spent')->default(0)->comment('Time in seconds');
            $table->json('click_paths')->nullable();
            
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_metrics');
    }
};

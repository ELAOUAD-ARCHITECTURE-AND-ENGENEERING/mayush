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
        Schema::create('analytics_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type')->index(); // e.g., gmv, visits, refund_rate
            $table->string('dimension')->default('global')->index(); // e.g., global, country_US
            $table->decimal('value', 15, 2)->default(0.00);
            $table->date('date')->index();
            $table->timestamps();
            
            // Unique composite index to prevent duplicate entries for the same day
            $table->unique(['metric_type', 'dimension', 'date'], 'analytics_daily_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_summaries');
    }
};

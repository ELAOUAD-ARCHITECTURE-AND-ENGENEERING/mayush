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
        Schema::table('visitor_metrics', function (Blueprint $table) {
            $table->index(['url', 'created_at']);
            $table->index(['session_id', 'created_at']);
        });

        Schema::create('analytics_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('revenue', 15, 2)->default(0);
            $table->integer('visits')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->decimal('aov', 15, 2)->default(0);
            $table->integer('orders')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_summaries');
        
        Schema::table('visitor_metrics', function (Blueprint $table) {
            $table->dropIndex(['url', 'created_at']);
            $table->dropIndex(['session_id', 'created_at']);
        });
    }
};

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
            $table->index(['created_at', 'is_entry']);
            $table->index(['created_at', 'is_exit']);
        });

        Schema::table('health_metrics', function (Blueprint $table) {
            $table->index(['created_at', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_metrics', function (Blueprint $table) {
            //
        });
    }
};

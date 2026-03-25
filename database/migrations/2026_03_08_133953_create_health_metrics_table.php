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
        Schema::create('health_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // error, latency, cpu, ram, uptime
            $table->string('source')->nullable()->index(); // frontend, backend, database
            $table->double('value')->nullable();
            $table->string('unit')->nullable(); // ms, %, byte
            $table->text('message')->nullable(); // Error message or description
            $table->json('context')->nullable(); // Stack trace, request data, etc.
            $table->timestamp('created_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_metrics');
    }
};

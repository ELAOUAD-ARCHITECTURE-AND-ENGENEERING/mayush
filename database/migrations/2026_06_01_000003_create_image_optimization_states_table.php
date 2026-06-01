<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_optimization_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->nullable()->constrained('uploads')->nullOnDelete();
            $table->string('source_kind', 20)->default('upload');
            $table->string('disk', 50);
            $table->string('source_path', 500);
            $table->string('source_fingerprint')->nullable();
            $table->string('recipe_version', 50)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('optimized_at')->nullable();
            $table->timestamps();

            $table->unique(['source_kind', 'disk', 'source_path'], 'image_optimization_source_unique');
            $table->index(['status', 'last_checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_optimization_states');
    }
};

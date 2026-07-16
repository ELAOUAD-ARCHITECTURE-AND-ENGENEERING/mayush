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
        Schema::create('bot_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('intent_code')->nullable();
            $table->string('node_key');
            $table->string('node_type');
            $table->text('message_template')->nullable();
            $table->json('options')->nullable();
            $table->string('next_node')->nullable();
            $table->string('fallback_node')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_nodes');
    }
};

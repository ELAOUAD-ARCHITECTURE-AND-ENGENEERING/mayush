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
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_conversation_id')->index();
            $table->string('sender_type')->default('guest'); // guest, user, agent, system
            $table->unsignedBigInteger('sender_id')->nullable()->index();
            $table->text('message');
            $table->timestamps();

            $table->foreign('support_conversation_id')->references('id')->on('support_conversations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};

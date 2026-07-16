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
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->boolean('bot_enabled')->default(true)->after('status');
            $table->string('conversation_state')->default('NEW')->after('bot_enabled');
            $table->string('active_intent_id')->nullable()->after('conversation_state');
            $table->integer('frustration_score')->default(0)->after('active_intent_id');
            $table->string('language', 10)->default('en')->after('frustration_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropColumn(['bot_enabled', 'conversation_state', 'active_intent_id', 'frustration_score', 'language']);
        });
    }
};

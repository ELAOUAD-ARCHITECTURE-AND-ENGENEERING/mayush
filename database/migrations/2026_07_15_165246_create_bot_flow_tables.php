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
        Schema::create('support_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'PD', 'PY'
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('display_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('support_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('support_categories')->onDelete('cascade');
            $table->string('case_code')->unique(); // e.g., 'OR-002'
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('eligible_user_types')->default('all');
            $table->string('priority')->default('normal');
            $table->string('department')->nullable();
            $table->string('status')->default('active');
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('case_question_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('support_cases')->onDelete('cascade');
            $table->string('language')->default('en');
            $table->text('question');
            $table->json('keywords')->nullable();
            $table->integer('weight')->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('case_required_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('support_cases')->onDelete('cascade');
            $table->string('field_key');
            $table->string('label');
            $table->string('field_type')->default('text'); // text, number, email
            $table->boolean('required')->default(true);
            $table->string('validation_rule')->nullable();
            $table->text('bot_prompt')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('case_resolution_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('support_cases')->onDelete('cascade');
            $table->integer('step_order');
            $table->string('step_type'); // message, action, question, escalate
            $table->text('message_template')->nullable();
            $table->string('action_key')->nullable();
            $table->string('success_transition')->nullable(); // e.g., 'next_step', 'resolve'
            $table->string('failure_transition')->nullable();
            $table->timestamps();
        });

        Schema::create('case_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('support_cases')->onDelete('cascade');
            $table->string('solution_code');
            $table->string('title');
            $table->text('content');
            $table->json('conditions')->nullable();
            $table->string('language')->default('en');
            $table->string('status')->default('active');
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('case_escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('support_cases')->onDelete('cascade');
            $table->string('rule_type'); // e.g. 'failed_solutions'
            $table->string('operator')->default('>=');
            $table->integer('threshold');
            $table->string('priority')->default('normal');
            $table->string('target_department')->nullable();
            $table->text('handoff_message')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('chatbot_collected_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('support_conversations')->onDelete('cascade');
            $table->foreignId('case_id')->constrained('support_cases')->onDelete('cascade');
            $table->string('field_key');
            $table->text('field_value');
            $table->boolean('is_sensitive')->default(false);
            $table->timestamp('collected_at')->useCurrent();
        });

        Schema::create('chatbot_resolution_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('support_conversations')->onDelete('cascade');
            $table->foreignId('case_id')->constrained('support_cases')->onDelete('cascade');
            $table->foreignId('solution_id')->nullable()->constrained('case_solutions')->nullOnDelete();
            $table->integer('attempt_number');
            $table->string('result')->nullable();
            $table->boolean('customer_confirmed')->nullable();
            $table->timestamps();
        });

        Schema::create('chatbot_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('support_conversations')->onDelete('cascade');
            $table->foreignId('case_id')->nullable()->constrained('support_cases')->nullOnDelete();
            $table->string('reason');
            $table->string('priority')->default('normal');
            $table->string('target_department')->nullable();
            $table->json('summary')->nullable();
            $table->integer('assigned_agent_id')->nullable();
            $table->timestamp('escalated_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        // Add columns to existing support_conversations table
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->foreignId('active_case_id')->nullable()->after('active_intent_id')->constrained('support_cases')->nullOnDelete();
            $table->integer('current_step')->nullable()->after('active_case_id');
            $table->integer('attempt_number')->default(1)->after('current_step');
            $table->integer('bot_turn_count')->default(0)->after('attempt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropForeign(['active_case_id']);
            $table->dropColumn(['active_case_id', 'current_step', 'attempt_number', 'bot_turn_count']);
        });

        Schema::dropIfExists('chatbot_escalations');
        Schema::dropIfExists('chatbot_resolution_attempts');
        Schema::dropIfExists('chatbot_collected_values');
        Schema::dropIfExists('case_escalation_rules');
        Schema::dropIfExists('case_solutions');
        Schema::dropIfExists('case_resolution_steps');
        Schema::dropIfExists('case_required_fields');
        Schema::dropIfExists('case_question_variants');
        Schema::dropIfExists('support_cases');
        Schema::dropIfExists('support_categories');
    }
};

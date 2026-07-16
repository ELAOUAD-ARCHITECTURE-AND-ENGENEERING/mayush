<?php

namespace App\Services\Bot;

use App\Models\SupportConversation;
use App\Enums\BotState;
use Illuminate\Support\Facades\DB;

class EscalationEngine
{
    public function isFrustrationDetected(string $message): bool
    {
        $message = strtolower(trim($message));
        $keywords = ['angry', 'useless', 'unacceptable', 'frustrated', 'terrible', 'worst'];
        foreach ($keywords as $word) {
            if (str_contains($message, $word)) {
                return true;
            }
        }
        return false;
    }

    public function isAgentRequested(string $message): bool
    {
        $message = strtolower(trim($message));
        $keywords = [
            // English
            'human', 'agent', 'support', 'manager', 'person', 'real person', 'real man', 'live agent', 'customer service', 'representative', 'speak to', 'talk to',
            // French
            'humain', 'agent', 'support', 'manager', 'personne', 'personne réelle', 'conseiller', 'conseillère', 'service client', 'parler à', 'direct',
            // Arabic
            'عميل', 'وكيل', 'إنسان', 'بشر', 'شخص', 'دعم', 'مدير', 'تحدث', 'كلم', 'بشري'
        ];
        foreach ($keywords as $word) {
            if (str_contains($message, $word)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Executes the escalation sequence. Mutes bot and triggers UI updates.
     */
    public function escalate(SupportConversation $conversation, string $reason): void
    {
        $conversation->bot_enabled = false;
        $conversation->conversation_state = BotState::WAITING_FOR_AGENT->value;
        $conversation->escalation_reason = $reason;
        $conversation->save();
        
        $conversation->messages()->create([
            'sender_type' => 'system',
            'message' => 'An agent will join the conversation soon. Please hold on and do not close the conversation.'
        ]);
        
        // Handoff Record (Section 19)
        $caseId = $conversation->active_case_id;
        $department = 'Support';
        $priority = 'normal';
        
        if ($caseId) {
            $case = DB::table('support_cases')->where('id', $caseId)->first();
            if ($case) {
                $department = $case->department;
                $priority = $case->priority;
            }
        }

        $collected = DB::table('chatbot_collected_values')
                       ->where('conversation_id', $conversation->id)
                       ->pluck('field_value', 'field_key')
                       ->toArray();
                       
        $attempts = DB::table('chatbot_resolution_attempts')
                       ->where('conversation_id', $conversation->id)
                       ->get()
                       ->toArray();

        $summary = json_encode([
            'collected_data' => $collected,
            'resolution_attempts' => $attempts,
            'bot_turn_count' => $conversation->bot_turn_count
        ]);
        
        DB::table('chatbot_escalations')->insert([
            'conversation_id' => $conversation->id,
            'case_id' => $caseId,
            'reason' => $reason,
            'priority' => $priority,
            'target_department' => $department,
            'summary' => $summary,
            'escalated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

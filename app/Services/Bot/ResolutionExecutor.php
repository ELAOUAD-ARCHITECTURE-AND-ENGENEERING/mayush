<?php

namespace App\Services\Bot;

use App\Models\SupportConversation;
use App\Enums\BotState;
use Illuminate\Support\Facades\DB;

class ResolutionExecutor
{
    protected $builder;
    protected $dataService;

    public function __construct()
    {
        $this->builder = new MessageBuilder();
        $this->dataService = new DataActionService();
    }

    public function executeResolution(SupportConversation $conversation)
    {
        $caseId = $conversation->active_case_id;
        
        if (!$caseId) {
            return;
        }

        // Get the current step
        $currentStepNum = $conversation->current_step ?? 1;
        
        $step = DB::table('case_resolution_steps')
                  ->where('case_id', $caseId)
                  ->where('step_order', $currentStepNum)
                  ->first();

        if (!$step) {
            // Fallback resolution: display the case description and offer to escalate
            $case = DB::table('support_cases')->where('id', $caseId)->first();
            $caseName = $case ? translate($case->name) : translate('this issue');
            $caseDesc = $case && $case->description ? translate($case->description) : translate('I don\'t have automated resolution steps for this specific issue yet.');
            
            $solutionText = translate("I see you need help with") . " **" . $caseName . "**.\n\n" . $caseDesc . "\n\n" . translate("Would you like to speak to a human agent to resolve this?");
            
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => $solutionText
            ]);

            $msg = $this->builder->build(
                "Please select an option:",
                ["Speak to a human", "Go back to main menu"]
            );
            
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => $msg
            ]);

            $conversation->conversation_state = BotState::INTENT_SELECTION->value;
            $conversation->save();
            return;
        }

        // Get collected data to pass to action
        $collected = DB::table('chatbot_collected_values')
                       ->where('conversation_id', $conversation->id)
                       ->where('case_id', $caseId)
                       ->pluck('field_value', 'field_key')
                       ->toArray();

        $solutionText = '';

        if ($step->step_type == 'action' && $step->action_key) {
            $action = $step->action_key;
            if (method_exists($this->dataService, $action)) {
                $solutionText = $this->dataService->{$action}($conversation, $collected);
            } else {
                $solutionText = translate("I have checked the system based on your details, but I cannot find a specific status at the moment.");
            }
        } elseif ($step->step_type == 'message' && $step->message_template) {
            $solutionText = translate($step->message_template);
        }

        // Send the solution
        $conversation->messages()->create([
            'sender_type' => 'system',
            'message' => $solutionText
        ]);

        // Record attempt
        DB::table('chatbot_resolution_attempts')->insert([
            'conversation_id' => $conversation->id,
            'case_id' => $caseId,
            'attempt_number' => $conversation->attempt_number ?? 1,
            'result' => 'presented',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ask for confirmation
        if ($step->success_transition == 'confirm') {
            $msg = $this->builder->build(
                "Did this solve your problem?",
                ["Yes", "No"]
            );
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => $msg
            ]);
            $conversation->conversation_state = BotState::WAITING_CONFIRMATION->value;
        } else {
            // Auto resolve if no confirmation needed
            $conversation->conversation_state = BotState::RESOLVED_BY_BOT->value;
        }
        
        $conversation->save();
    }
}

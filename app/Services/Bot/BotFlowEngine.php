<?php

namespace App\Services\Bot;

use App\Models\SupportConversation;
use App\Enums\BotState;
use Illuminate\Support\Facades\DB;

class BotFlowEngine
{
    protected $builder;
    protected $escalator;
    protected $collector;
    protected $executor;
    protected $security;

    public function __construct()
    {
        $this->builder = new MessageBuilder();
        $this->escalator = new EscalationEngine();
        $this->collector = new DataCollector();
        $this->executor = new ResolutionExecutor();
        $this->security = new SecurityGuard();
    }

    public function process(SupportConversation $conversation, string $message): void
    {
        // 0. Sanitize message
        $message = $this->security->sanitizeMessage($message);
        
        // Intercept Go Back
        $cleanMessage = strtolower(trim($message));
        if ($cleanMessage === 'go back to main menu' || $cleanMessage === 'back' || $cleanMessage === 'main menu') {
            $this->resetToGreeting($conversation);
            return;
        }
        
        // 1. Check for max bot turns
        if ($conversation->bot_turn_count >= config('bot_escalation.max_bot_turns', 12)) {
            $this->escalator->escalate($conversation, 'Maximum bot turns reached.');
            return;
        }

        // 2. Global immediate escalation checks
        if ($this->escalator->isFrustrationDetected($message) || $this->escalator->isAgentRequested($message)) {
            $this->escalator->escalate($conversation, 'Customer requested human or showed frustration.');
            return;
        }

        $conversation->bot_turn_count++;
        $conversation->save();

        // 3. State Machine
        $state = BotState::tryFrom($conversation->conversation_state) ?? BotState::NEW;

        switch ($state) {
            case BotState::BOT_GREETING:
                $this->handleCategorySelection($conversation, $message);
                break;
                
            case BotState::INTENT_SELECTION:
                $this->handleCaseSelection($conversation, $message);
                break;
                
            case BotState::COLLECTING_DATA:
                $this->collector->handleInput($conversation, $message);
                
                // If it finished collecting, DataCollector updates state to FETCHING_CONTEXT
                $conversation->refresh();
                if ($conversation->conversation_state == BotState::FETCHING_CONTEXT->value) {
                    $this->executor->executeResolution($conversation);
                }
                break;
                
            case BotState::WAITING_CONFIRMATION:
                $this->handleConfirmation($conversation, $message);
                break;
                
            default:
                $this->processUnrecognized($conversation);
                break;
        }
    }

    protected function handleCategorySelection(SupportConversation $conversation, string $message)
    {
        // 1. Check if the user selected a category pill (translated or English)
        $category = null;
        $allCategories = DB::table('support_categories')->get();
        foreach ($allCategories as $cat) {
            if (trim(strtolower($message)) === strtolower(trim($cat->name)) || 
                trim(strtolower($message)) === strtolower(trim(translate($cat->name)))) {
                $category = $cat;
                break;
            }
        }
        
        // 2. Or fallback to intent matching if they type instead of click
        if (!$category) {
            $matcher = new IntentMatcher();
            $intent = $matcher->match($message);
            if ($intent) {
                // If intent matches a direct case, we can jump to COLLECTING_DATA
                // For now, let's keep it simple and just force clicking pills.
            }
        }
        
        if ($category) {
            $cases = DB::table('support_cases')
                        ->where('category_id', $category->id)
                        ->pluck('name')
                        ->toArray();
            
            if (empty($cases)) {
                $msg = $this->builder->build(
                    translate("Automated support for '" . $category->name . "' is not yet configured. Would you like to speak to a human or go back?"),
                    [translate("Speak to a human"), translate("Go back to main menu")]
                );
                
                $conversation->messages()->create([
                    'sender_type' => 'system',
                    'message' => $msg
                ]);
                
                $conversation->conversation_state = BotState::INTENT_SELECTION->value;
                $conversation->save();
                return;
            }
            
            $msg = $this->builder->build(
                translate("Please select the issue you're facing in " . $category->name . ":"),
                array_map('translate', $cases)
            );
            
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => $msg
            ]);
            
            $conversation->conversation_state = BotState::INTENT_SELECTION->value;
            $conversation->frustration_score = 0;
            $conversation->save();
        } else {
            $this->processUnrecognized($conversation);
        }
    }

    public function resetToGreeting(SupportConversation $conversation)
    {
        $categories = DB::table('support_categories')
            ->orderBy('display_order')
            ->pluck('name')
            ->toArray();
            
        $msg = $this->builder->build(
            "Hello! I am your Mayush automated assistant. How can I help you today?",
            $categories
        );
        
        $conversation->messages()->create([
            'sender_type' => 'system',
            'message' => $msg
        ]);
        
        $conversation->conversation_state = BotState::BOT_GREETING->value;
        $conversation->active_case_id = null;
        $conversation->current_step = null;
        $conversation->attempt_number = 1;
        $conversation->save();
    }

    protected function handleCaseSelection(SupportConversation $conversation, string $message)
    {
        $case = null;
        $allCases = DB::table('support_cases')->get();
        foreach ($allCases as $c) {
            if (trim(strtolower($message)) === strtolower(trim($c->name)) || 
                trim(strtolower($message)) === strtolower(trim(translate($c->name)))) {
                $case = $c;
                break;
            }
        }
        
        if ($case) {
            // Check for immediate escalation
            $escalation = DB::table('case_escalation_rules')
                            ->where('case_id', $case->id)
                            ->where('rule_type', 'immediate')
                            ->first();
                            
            if ($escalation) {
                $this->escalator->escalate($conversation, $escalation->handoff_message ?? 'Case requires immediate escalation.');
                return;
            }

            $conversation->active_case_id = $case->id;
            $conversation->conversation_state = BotState::COLLECTING_DATA->value;
            $conversation->current_step = 1;
            $conversation->attempt_number = 1;
            $conversation->frustration_score = 0;
            $conversation->save();
            
            // Kick off data collection
            if ($this->security->requiresSecurityWarning($conversation)) {
                $conversation->messages()->create([
                    'sender_type' => 'system',
                    'message' => translate('For your security, never share your full card number, CVV, banking password, or verification code in this chat.')
                ]);
            }
            $this->collector->askNextField($conversation);
            
            // Re-check state in case there were NO fields to collect
            $conversation->refresh();
            if ($conversation->conversation_state == BotState::FETCHING_CONTEXT->value) {
                $this->executor->executeResolution($conversation);
            }
        } else {
            $this->processUnrecognized($conversation);
        }
    }

    protected function handleConfirmation(SupportConversation $conversation, string $message)
    {
        $clean = strtolower(trim($message));
        $yesMatch = str_contains($clean, 'yes') || str_contains($clean, 'solved') || 
                    str_contains($clean, strtolower(translate('Yes'))) || str_contains($clean, strtolower(translate('Solved'))) ||
                    str_contains($clean, 'connect') || str_contains($clean, strtolower(translate('Yes, connect to agent')));
                    
        $noMatch = str_contains($clean, 'no') || str_contains($clean, 'help') || 
                   str_contains($clean, strtolower(translate('No'))) || str_contains($clean, strtolower(translate('Help'))) ||
                   str_contains($clean, 'main menu') || str_contains($clean, 'back') ||
                   str_contains($clean, strtolower(translate('No, go back to main menu')));

        if ($conversation->current_step == 999) {
            if ($yesMatch) {
                $this->escalator->escalate($conversation, 'User accepted human escalation.');
            } else {
                $this->resetToGreeting($conversation);
            }
            return;
        }

        if ($yesMatch) {
            $conversation->conversation_state = BotState::RESOLVED_BY_BOT->value;
            $conversation->save();
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => translate('Great! If you need anything else, feel free to start a new chat.')
            ]);
        } elseif ($noMatch) {
            $msg = $this->builder->build(
                translate("I'm sorry to hear that. Would you like to connect with a human agent to resolve this?"),
                [translate("Yes, connect to agent"), translate("No, go back to main menu")]
            );
            
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => $msg
            ]);
            
            $conversation->current_step = 999;
            $conversation->save();
        } else {
            $this->processUnrecognized($conversation);
        }
    }

    protected function processUnrecognized(SupportConversation $conversation)
    {
        $conversation->frustration_score++;
        $conversation->save();
        
        if ($conversation->frustration_score >= config('bot_escalation.unrecognized_messages', 2)) {
            $this->escalator->escalate($conversation, 'Failed to understand multiple times.');
        } else {
            $categories = DB::table('support_categories')
                ->orderBy('display_order')
                ->pluck('name')
                ->toArray();

            $msg = $this->builder->build(
                translate('I did not understand your message. Could you please clarify your idea or select one of the options below?'),
                array_map('translate', $categories)
            );

            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => $msg
            ]);
            
            // Reset state to BOT_GREETING so user can choose from the category list again
            $conversation->conversation_state = BotState::BOT_GREETING->value;
            $conversation->save();
        }
    }
}

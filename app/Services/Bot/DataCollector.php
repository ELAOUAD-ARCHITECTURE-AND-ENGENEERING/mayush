<?php

namespace App\Services\Bot;

use App\Models\SupportConversation;
use App\Enums\BotState;
use Illuminate\Support\Facades\DB;

class DataCollector
{
    protected $builder;

    public function __construct()
    {
        $this->builder = new MessageBuilder();
    }

    public function askNextField(SupportConversation $conversation)
    {
        $caseId = $conversation->active_case_id;
        
        if (!$caseId) {
            return;
        }

        // Get all required fields for this case
        $fields = DB::table('case_required_fields')
                    ->where('case_id', $caseId)
                    ->orderBy('display_order', 'asc')
                    ->get();
                    
        // Get already collected fields
        $collected = DB::table('chatbot_collected_values')
                       ->where('conversation_id', $conversation->id)
                       ->where('case_id', $caseId)
                       ->pluck('field_key')
                       ->toArray();

        // Find the first field that hasn't been collected
        foreach ($fields as $field) {
            if (!in_array($field->field_key, $collected)) {
                // We need to ask this field
                $prompt = $field->bot_prompt ? translate($field->bot_prompt) : sprintf(translate('Please provide %s.'), translate($field->label));
                $conversation->messages()->create([
                    'sender_type' => 'system',
                    'message' => $prompt
                ]);
                return; // Wait for user reply
            }
        }

        // If we reach here, all fields are collected
        $conversation->conversation_state = BotState::FETCHING_CONTEXT->value;
        $conversation->save();
    }

    public function handleInput(SupportConversation $conversation, string $message)
    {
        $caseId = $conversation->active_case_id;
        
        if (!$caseId) {
            return;
        }

        // Find which field we are currently asking for
        $fields = DB::table('case_required_fields')
                    ->where('case_id', $caseId)
                    ->orderBy('display_order', 'asc')
                    ->get();
                    
        $collected = DB::table('chatbot_collected_values')
                       ->where('conversation_id', $conversation->id)
                       ->where('case_id', $caseId)
                       ->pluck('field_key')
                       ->toArray();

        foreach ($fields as $field) {
            if (!in_array($field->field_key, $collected)) {
                // This is the field the user just answered
                
                // TODO: Run validation_rule if any
                
                // Save it
                DB::table('chatbot_collected_values')->insert([
                    'conversation_id' => $conversation->id,
                    'case_id' => $caseId,
                    'field_key' => $field->field_key,
                    'field_value' => $message,
                    'is_sensitive' => false,
                    'collected_at' => now(),
                ]);
                
                // Ask the next field
                $this->askNextField($conversation);
                return;
            }
        }
    }
}

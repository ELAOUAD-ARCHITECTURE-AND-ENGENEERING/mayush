<?php

namespace App\Services\Bot;

use App\Models\BotIntent;

class IntentMatcher
{
    /**
     * Extracts intent from user text using deterministic keyword matching for MVP.
     */
    public function match(string $message): ?BotIntent
    {
        $message = strtolower(trim($message));
        
        // MVP basic keyword mapping (English Base)
        $keywords = [
            'order' => 'ORDER_STATUS',
            'track' => 'ORDER_STATUS',
            'where is' => 'ORDER_STATUS',
            
            'pay' => 'PAYMENT_SUPPORT',
            'card' => 'PAYMENT_SUPPORT',
            'declined' => 'PAYMENT_SUPPORT',
            
            'delivery' => 'DELIVERY_ESTIMATE',
            'arrive' => 'DELIVERY_ESTIMATE',
            
            'return' => 'RETURN_ELIGIBILITY',
            'refund' => 'REFUND_STATUS',
            
            'human' => 'ESCALATION_REQUEST',
            'agent' => 'ESCALATION_REQUEST',
            'support' => 'ESCALATION_REQUEST',
            'manager' => 'ESCALATION_REQUEST',
            
            'angry' => 'CUSTOMER_COMPLAINT',
            'useless' => 'CUSTOMER_COMPLAINT',
        ];

        foreach ($keywords as $word => $intentCode) {
            if (str_contains($message, $word)) {
                // In production, fetch from DB: BotIntent::where('intent_code', $intentCode)->first();
                $intent = new BotIntent();
                $intent->intent_code = $intentCode;
                $intent->is_sensitive = in_array($intentCode, ['ESCALATION_REQUEST', 'CUSTOMER_COMPLAINT']);
                return $intent;
            }
        }

        return null;
    }
}

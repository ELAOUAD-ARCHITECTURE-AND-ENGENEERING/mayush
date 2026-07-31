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
        
        // MVP basic keyword mapping (English, French, Arabic)
        $keywords = [
            // Order Status
            'order' => 'ORDER_STATUS',
            'track' => 'ORDER_STATUS',
            'where is' => 'ORDER_STATUS',
            'commande' => 'ORDER_STATUS',
            'suivi' => 'ORDER_STATUS',
            'طلب' => 'ORDER_STATUS',
            'تتبع' => 'ORDER_STATUS',
            'أين' => 'ORDER_STATUS',

            // Payment Support
            'pay' => 'PAYMENT_SUPPORT',
            'card' => 'PAYMENT_SUPPORT',
            'declined' => 'PAYMENT_SUPPORT',
            'paiement' => 'PAYMENT_SUPPORT',
            'carte' => 'PAYMENT_SUPPORT',
            'refusé' => 'PAYMENT_SUPPORT',
            'دفع' => 'PAYMENT_SUPPORT',
            'بطاقة' => 'PAYMENT_SUPPORT',

            // Delivery Estimate
            'delivery' => 'DELIVERY_ESTIMATE',
            'arrive' => 'DELIVERY_ESTIMATE',
            'livraison' => 'DELIVERY_ESTIMATE',
            'arriver' => 'DELIVERY_ESTIMATE',
            'توصيل' => 'DELIVERY_ESTIMATE',
            'وصول' => 'DELIVERY_ESTIMATE',

            // Returns & Refunds
            'return' => 'RETURN_ELIGIBILITY',
            'refund' => 'REFUND_STATUS',
            'retour' => 'RETURN_ELIGIBILITY',
            'remboursement' => 'REFUND_STATUS',
            'إرجاع' => 'RETURN_ELIGIBILITY',
            'استرجاع' => 'REFUND_STATUS',

            // Escalation
            'human' => 'ESCALATION_REQUEST',
            'agent' => 'ESCALATION_REQUEST',
            'support' => 'ESCALATION_REQUEST',
            'manager' => 'ESCALATION_REQUEST',
            'humain' => 'ESCALATION_REQUEST',
            'conseiller' => 'ESCALATION_REQUEST',
            'عميل' => 'ESCALATION_REQUEST',
            'وكيل' => 'ESCALATION_REQUEST',
            'إنسان' => 'ESCALATION_REQUEST',

            // Complaints
            'angry' => 'CUSTOMER_COMPLAINT',
            'useless' => 'CUSTOMER_COMPLAINT',
            'inacceptable' => 'CUSTOMER_COMPLAINT',
            'سيء' => 'CUSTOMER_COMPLAINT',
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

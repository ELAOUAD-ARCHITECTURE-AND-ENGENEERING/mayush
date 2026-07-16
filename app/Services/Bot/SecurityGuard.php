<?php

namespace App\Services\Bot;

use App\Models\SupportConversation;

class SecurityGuard
{
    /**
     * Scans message for sensitive data like credit card numbers or CVVs.
     * Returns a redacted string if found.
     */
    public function sanitizeMessage(string $message): string
    {
        // Simple regex for 13-19 digit card numbers (allowing spaces/dashes)
        $cardRegex = '/(?:\d[ -]*?){13,19}/';
        
        $message = preg_replace_callback($cardRegex, function ($matches) {
            $stripped = preg_replace('/[ -]/', '', $matches[0]);
            if (strlen($stripped) >= 13 && strlen($stripped) <= 19) {
                return '[REDACTED CARD NUMBER]';
            }
            return $matches[0];
        }, $message);

        // Simple regex for 3-4 digit CVV following keywords like "cvv", "cvc", "security code"
        $cvvRegex = '/(cvv|cvc|security code|code)[\s:-]*(\d{3,4})/i';
        $message = preg_replace($cvvRegex, '$1 [REDACTED]', $message);

        return $message;
    }
    
    /**
     * Checks if the active case requires a security warning.
     */
    public function requiresSecurityWarning(SupportConversation $conversation): bool
    {
        // For MVP, checking if it's a payment case
        if ($conversation->active_case_id) {
            $case = \Illuminate\Support\Facades\DB::table('support_cases')
                        ->join('support_categories', 'support_cases.category_id', '=', 'support_categories.id')
                        ->where('support_cases.id', $conversation->active_case_id)
                        ->select('support_categories.code')
                        ->first();
                        
            if ($case && $case->code === 'PY') {
                return config('bot_escalation.payment_security_warning', true);
            }
        }
        
        return false;
    }
}

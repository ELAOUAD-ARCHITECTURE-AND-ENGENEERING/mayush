<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Send a security alert.
     * 
     * @param string $message
     * @param string $level 'critical'|'warning'|'info'
     * @param array $context
     */
    public function send(string $message, string $level = 'warning', array $context = []): void
    {
        // For now, we log to a dedicated security channel.
        // This can be easily extended to send SMS (SendSmsService), Slack webhooks, or Emails.
        Log::channel('stack')->log($level, "[SECURITY_ALERT] $message", $context);

        if ($level === 'critical') {
            // Logic for immediate notification (e.g. SMS via existing SendSmsService)
            // Example: (new SendSmsService())->sendAlert($message);
        }
    }
}

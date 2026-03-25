<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Events\SecurityAlert;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Http;

class SecurityEventSubscriber
{
    /**
     * Send a notification to Slack for critical security events.
     */
    private function notifySlack($message, $level = 'info')
    {
        $webhookUrl = env('SLACK_SECURITY_WEBHOOK_URL');

        if (!$webhookUrl) {
            \Log::info("Slack Notification (No Webhook): [{$level}] {$message}");
            return;
        }

        try {
            Http::post($webhookUrl, [
                'text' => $message,
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send Slack notification: " . $e->getMessage());
        }
    }

    /**
     * Handle user login events.
     */
    public function handleUserLogin(Login $event) 
    {
        $user = $event->user;
        AuditLog::create([
            'admin_user_id'  => $user->user_type === 'admin' ? $user->id : null,
            'target_user_id' => $user->id,
            'action_type'    => 'LOGIN',
            'description'    => "User logged in: {$user->email}",
            'ip_address'     => Request::ip(),
        ]);
    }

    /**
     * Handle user logout events.
     */
    public function handleUserLogout(Logout $event) 
    {
        if ($event->user) {
            AuditLog::create([
                'target_user_id' => $event->user->id,
                'action_type'    => 'LOGOUT',
                'description'    => "User logged out: {$event->user->email}",
                'ip_address'     => Request::ip(),
            ]);
        }
    }

    /**
     * Handle failed login events.
     */
    public function handleFailedLogin(Failed $event) 
    {
        $email = $event->credentials['email'] ?? 'unknown';
        $ip = Request::ip();
        
        AuditLog::create([
            'action_type'    => 'FAILED_LOGIN',
            'description'    => "Failed login attempt for email: {$email}",
            'ip_address'     => $ip,
        ]);

        // Alert Slack
        $this->notifySlack("⚠️ *Failed login attempt* detected.\n*Email:* `{$email}`\n*IP:* `{$ip}`", 'warning');
    }

    public function handleSecurityAlert(SecurityAlert $event)
    {
        $this->notifySlack($event->message, $event->level);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            Login::class,
            [SecurityEventSubscriber::class, 'handleUserLogin']
        );

        $events->listen(
            Logout::class,
            [SecurityEventSubscriber::class, 'handleUserLogout']
        );

        $events->listen(
            Failed::class,
            [SecurityEventSubscriber::class, 'handleFailedLogin']
        );

        $events->listen(
            SecurityAlert::class,
            [SecurityEventSubscriber::class, 'handleSecurityAlert']
        );
    }
}

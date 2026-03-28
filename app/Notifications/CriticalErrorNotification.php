<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use App\Events\CriticalSystemError;
use Carbon\Carbon;

class CriticalErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $event;

    /**
     * The number of times the queued notification may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the notification.
     */
    public $backoff = [60, 300, 1800]; // 1m, 5m, 30m

    /**
     * Create a new notification instance.
     */
    public function __construct(CriticalSystemError $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->error()
                    ->subject('[Mayush Critical System Alert] ' . $this->event->component . ' Degraded')
                    ->greeting('Hello System Administrator,')
                    ->line('A critical error has occurred in the Mayush platform.')
                    ->line('**Component:** ' . $this->event->component)
                    ->line('**Severity:** ' . ucfirst($this->event->severity))
                    ->line('**Message:** ' . $this->event->message)
                    ->line('**Timestamp:** ' . Carbon::now()->toDateTimeString())
                    ->line('**Details:** ' . json_encode($this->event->details))
                    ->action('Investigate System Logs', url(env('APP_URL') . '/admin'));
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $color = ($this->event->severity === 'critical') ? '#ff0000' : '#ffa500';

        return (new SlackMessage)
            ->from('Mayush Watchdog')
            ->to(env('SLACK_SECURITY_CHANNEL', '#security-alerts'))
            ->attachment(function ($attachment) use ($color) {
                $attachment->title("System Alert: " . $this->event->component)
                           ->content($this->event->message . "\n" . json_encode($this->event->details))
                           ->color($color)
                           ->timestamp(Carbon::now());
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

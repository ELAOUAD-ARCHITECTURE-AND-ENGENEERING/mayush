<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['slack']; // Can be extended to 'mail', 'discord' via webhook
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack($notifiable)
    {
        $level = $this->data['level'] ?? 'info';
        $color = ($level === 'critical') ? '#ff0000' : '#ffa500';

        return (new SlackMessage)
            ->from('Mayush Security Bot')
            ->to(env('SLACK_SECURITY_CHANNEL', '#security-alerts'))
            ->attachment(function ($attachment) use ($color) {
                $attachment->title("Security Alert: " . $this->data['type'])
                           ->content($this->data['message'])
                           ->color($color)
                           ->timestamp(\Carbon\Carbon::now());
            });
    }
}

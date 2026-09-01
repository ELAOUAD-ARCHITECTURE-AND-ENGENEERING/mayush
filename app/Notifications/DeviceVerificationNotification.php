<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeviceVerificationNotification extends Notification
{
    use Queueable;

    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Vérification de votre appareil — Mayush')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line('Une tentative de connexion a été détectée depuis un nouvel appareil.')
            ->line('Votre code de vérification est : **' . $this->code . '**')
            ->line('Ce code expire dans 10 minutes.')
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, changez immédiatement votre mot de passe.')
            ->salutation('L\'équipe Mayush');
    }
}

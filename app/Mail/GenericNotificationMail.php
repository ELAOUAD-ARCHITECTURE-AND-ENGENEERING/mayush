<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
        $this->onQueue('emails');
        $this->afterCommit = true;
    }

    public function build()
    {
        return $this->subject($this->payload['title'])
            ->view('emails.notification_v2')
            ->with(['payload' => $this->payload]);
    }
}

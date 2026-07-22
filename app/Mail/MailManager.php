<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class MailManager extends Mailable
{
    public $tries = 3;
    public $timeout = 60;

    use Queueable, SerializesModels;

    public $array;

    public function __construct($array)
    {
        $this->onQueue('emails');
        $this->array = $array;

        $targetLocale = $array['lang'] ?? $array['locale'] ?? session('locale') ?? env('DEFAULT_LANGUAGE', 'fr');
        $this->locale(!empty($targetLocale) ? $targetLocale : 'fr');
    }

    public function build()
    {
        $targetLocale = $this->locale ?: ($this->array['lang'] ?? $this->array['locale'] ?? session('locale') ?? env('DEFAULT_LANGUAGE', 'fr')) ?: 'fr';
        App::setLocale($targetLocale);

        return $this->view('emails.index')
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject($this->array['subject'])
                    ->with([
                        'content' => $this->array['content'],
                        'lang' => $targetLocale
                    ]);
    }
}

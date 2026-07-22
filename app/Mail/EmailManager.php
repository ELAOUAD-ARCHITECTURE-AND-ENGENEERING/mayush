<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;

class EmailManager extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    public $array;

    public function __construct($array)
    {
        $this->onQueue('emails');
        $this->afterCommit = true;
        
        $this->array = $array;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return isset($this->array['order_id']) 
            ? ['email', 'order:'.$this->array['order_id']] 
            : ['email'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $targetLocale = $this->locale ?: ($this->array['lang'] ?? $this->array['locale'] ?? session('locale') ?? env('DEFAULT_LANGUAGE', 'fr')) ?: 'fr';
        App::setLocale($targetLocale);

        return $this->view($this->array['view'])
                    ->from($this->array['from'] ?? env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject($this->array['subject']);
    }
}

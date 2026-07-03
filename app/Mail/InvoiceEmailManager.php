<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoiceEmailManager extends Mailable implements ShouldQueue
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
        return isset($this->array['order']->id) 
            ? ['email:invoice', 'order:'.$this->array['order']->id] 
            : ['email:invoice'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
     public function build()
     {
        return $this->view($this->array['view'])
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($this->array['subject'])
                ->with([
                    'order' => $this->array['order']
                ]);
     }
}

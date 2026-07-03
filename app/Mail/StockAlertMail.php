<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockAlertMail extends Mailable
{
    public $tries = 3;
    public $timeout = 60;

    use Queueable, SerializesModels;

    public $product;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($product, $user)
    {
        $this->onQueue('emails');
        $this->product = $product;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.stock_alert')
                    ->subject(translate('Good news! ').$this->product->getTranslation('name').translate(' is back in stock!'));
    }
}

<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecurityAlert
{
    use Dispatchable, SerializesModels;

    public $message;
    public $level;
    public $context;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $level = 'info', $context = [])
    {
        $this->message = $message;
        $this->level = $level;
        $this->context = $context;
    }
}

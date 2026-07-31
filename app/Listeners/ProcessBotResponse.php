<?php

namespace App\Listeners;

use App\Events\NewCustomerMessageReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Bot\IntentMatcher;
use App\Services\Bot\EscalationEngine;
use App\Enums\BotState;

class ProcessBotResponse implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NewCustomerMessageReceived $event): void
    {
        $conversation = $event->conversation;
        
        if (!$conversation->bot_enabled) {
            return;
        }

        if (!empty($conversation->language)) {
            \Illuminate\Support\Facades\App::setLocale($conversation->language);
        }
        
        $engine = new \App\Services\Bot\BotFlowEngine();
        $engine->process($conversation, $event->message);
    }
}

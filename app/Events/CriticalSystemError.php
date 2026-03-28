<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CriticalSystemError
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $component;
    public $message;
    public $details;
    public $severity;

    /**
     * Create a new event instance.
     */
    public function __construct(string $component, string $message, array $details = [], string $severity = 'high')
    {
        $this->component = $component;
        $this->message = $message;
        $this->details = $details;
        $this->severity = $severity;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}

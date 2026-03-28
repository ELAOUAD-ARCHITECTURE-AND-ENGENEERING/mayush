<?php

namespace App\Listeners;

use App\Events\CriticalSystemError;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CriticalErrorNotification;

class SendCriticalErrorNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CriticalSystemError $event): void
    {
        // Find the system administrator (usually ID 1 or role 'admin')
        $admin = User::where('user_type', 'admin')->first();
        
        if ($admin) {
            Notification::send($admin, new CriticalErrorNotification($event));
        }
    }
}

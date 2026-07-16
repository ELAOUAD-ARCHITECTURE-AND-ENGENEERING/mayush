<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpireGuestSupportChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support:expire-guest-chats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire inactive guest support conversations after 5 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = \Carbon\Carbon::now()->subMinutes(5);
        
        $expiredConversations = \App\Models\SupportConversation::whereNull('user_id')
            ->whereNotNull('guest_token')
            ->where('status', 'open')
            ->where('last_activity_at', '<', $threshold)
            ->get();
            
        foreach ($expiredConversations as $conversation) {
            $conversation->status = 'expired';
            $conversation->save();
            
            $conversation->messages()->create([
                'sender_type' => 'system',
                'message' => 'conversation expired'
            ]);
        }
        
        $this->info('Expired ' . $expiredConversations->count() . ' guest conversations.');
    }
}

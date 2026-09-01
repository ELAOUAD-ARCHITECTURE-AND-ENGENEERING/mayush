<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class PurgeUnverifiedUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:purge-unverified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge unverified customer accounts older than 24 hours.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subHours(24);

        $count = User::where('user_type', 'customer')
            ->whereNull('email_verified_at')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Purged {$count} unverified customer account(s) older than 24 hours.");

        return 0;
    }
}

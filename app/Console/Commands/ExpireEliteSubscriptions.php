<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EliteSubscription;

class ExpireEliteSubscriptions extends Command
{
    protected $signature = 'elite:expire';
    protected $description = 'Expire Elite subscriptions that have passed their expiry date.';

    public function handle(): void
    {
        $expired = EliteSubscription::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = $expired->count();

        if ($count === 0) {
            $this->info('No expired Elite subscriptions found.');
            return;
        }

        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'expired']);
        }

        $this->info("Expired {$count} Elite subscription(s) successfully.");
    }
}

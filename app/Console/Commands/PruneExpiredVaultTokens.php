<?php

namespace App\Console\Commands;

use App\Models\PaymentToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneExpiredVaultTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vault:prune-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate payment vault tokens whose card expiry date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = PaymentToken::pruneExpired();

        if ($count > 0) {
            Log::info("Vault Pruning: Deactivated {$count} expired token(s).");
            $this->info("Deactivated {$count} expired vault token(s).");
        } else {
            $this->info('No expired vault tokens found.');
        }

        return Command::SUCCESS;
    }
}

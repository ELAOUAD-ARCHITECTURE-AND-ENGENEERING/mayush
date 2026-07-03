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
    protected $signature = 'vault:prune-expired {--dry-run : Count expired active vault tokens without deactivating anything}';

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
        if ($this->option('dry-run')) {
            $count = $this->expiredActiveTokensQuery()->count();

            $this->warn('DRY RUN: no payment_tokens rows will be updated.');
            $this->line('Table: payment_tokens');
            $this->line('Conditions: is_active = true AND card expiry month/year is before the current month.');
            $this->info("Candidate expired active vault tokens: {$count}");

            return Command::SUCCESS;
        }

        $count = PaymentToken::pruneExpired();

        if ($count > 0) {
            Log::info("Vault Pruning: Deactivated {$count} expired token(s).");
            $this->info("Deactivated {$count} expired vault token(s).");
        } else {
            $this->info('No expired vault tokens found.');
        }

        return Command::SUCCESS;
    }

    private function expiredActiveTokensQuery()
    {
        $now = now();

        return PaymentToken::where('is_active', true)
            ->whereNotNull('card_expiry_year')
            ->whereNotNull('card_expiry_month')
            ->where(function ($query) use ($now) {
                $query->where('card_expiry_year', '<', $now->year)
                    ->orWhere(function ($inner) use ($now) {
                        $inner->where('card_expiry_year', $now->year)
                            ->where('card_expiry_month', '<', $now->month);
                    });
            });
    }
}

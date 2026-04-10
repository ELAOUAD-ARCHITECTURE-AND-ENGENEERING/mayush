<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FrequentlyBoughtProduct;

class PruneStaleFrequentlyBought extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:prune-affinities {--days=30 : Number of days before an automated affinity is considered stale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old, potentially stale automated affinities from frequently bought products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Pruning automated affinities older than $days days...");

        $deleted = FrequentlyBoughtProduct::where('source', 'automated')
            ->where('updated_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Successfully pruned $deleted stale affinities.");
    }
}

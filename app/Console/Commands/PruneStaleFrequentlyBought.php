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
    protected $signature = 'inventory:prune-affinities
        {--days=30 : Number of days before an automated affinity is considered stale}
        {--dry-run : Count stale automated affinities without deleting anything}';

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
        $days = (int) $this->option('days');

        if ($days < 0) {
            $this->error('The --days option must be zero or greater.');

            return Command::FAILURE;
        }

        $query = FrequentlyBoughtProduct::where('source', 'automated')
            ->where('updated_at', '<', now()->subDays($days));

        if ($this->option('dry-run')) {
            $count = (clone $query)->count();

            $this->warn('DRY RUN: no frequently_bought_products rows will be deleted.');
            $this->line('Table: frequently_bought_products');
            $this->line("Conditions: source = automated AND updated_at < now() - {$days} days");
            $this->info("Candidate stale automated affinities: {$count}");

            return Command::SUCCESS;
        }

        $this->info("Pruning automated affinities older than $days days...");

        $deleted = $query->delete();

        $this->info("Successfully pruned $deleted stale affinities.");

        return Command::SUCCESS;
    }
}

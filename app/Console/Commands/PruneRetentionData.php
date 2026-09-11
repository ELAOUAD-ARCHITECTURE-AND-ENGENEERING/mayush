<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneRetentionData extends Command
{
    protected $signature = 'data:prune
        {--dry-run : Show what would be deleted without deleting}
        {--table= : Prune a specific table only}';

    protected $description = 'Prune old rows from direct_prune_candidate_tables based on retention_days_suggestions';

    public function handle(): int
    {
        if (!config('mayush_retention.pruning_enabled')) {
            $this->warn('Pruning is disabled (mayush_retention.pruning_enabled = false).');
            return self::SUCCESS;
        }

        $candidates = config('mayush_retention.direct_prune_candidate_tables', []);
        $retentionDays = config('mayush_retention.retention_days_suggestions', []);
        $dryRun = $this->option('dry-run');
        $specificTable = $this->option('table');

        $totalDeleted = 0;

        foreach ($candidates as $table) {
            if ($specificTable && $table !== $specificTable) {
                continue;
            }

            $days = $retentionDays[$table] ?? null;
            if ($days === null) {
                $this->line("  Skipping <comment>{$table}</comment> — no retention period configured.");
                continue;
            }

            $cutoff = Carbon::now()->subDays($days);

            // Determine the date column to use
            $dateColumn = 'created_at';
            if (!DB::getSchemaBuilder()->hasColumn($table, 'created_at')) {
                // Common alternatives
                foreach (['failed_at', 'timestamp'] as $alt) {
                    if (DB::getSchemaBuilder()->hasColumn($table, $alt)) {
                        $dateColumn = $alt;
                        break 1;
                    }
                }
                if ($dateColumn === 'created_at') {
                    $this->line("  Skipping <comment>{$table}</comment> — no date column found.");
                    continue;
                }
            }

            $count = DB::table($table)->where($dateColumn, '<', $cutoff)->count();

            if ($count === 0) {
                $this->line("  <info>{$table}</info>: nothing to prune (retention: {$days}d).");
                continue;
            }

            if ($dryRun) {
                $this->line("  <info>{$table}</info>: would delete <comment>{$count}</comment> rows older than {$days} days.");
            } else {
                // Delete in batches to avoid locking
                $deleted = 0;
                do {
                    $batch = DB::table($table)->where($dateColumn, '<', $cutoff)->limit(1000)->delete();
                    $deleted += $batch;
                } while ($batch > 0);

                $this->line("  <info>{$table}</info>: deleted <comment>{$deleted}</comment> rows older than {$days} days.");
                $totalDeleted += $deleted;
            }
        }

        if ($dryRun) {
            $this->info('Dry run complete — no rows were deleted.');
        } else {
            $this->info("Pruning complete. Total rows deleted: {$totalDeleted}");
        }

        return self::SUCCESS;
    }
}

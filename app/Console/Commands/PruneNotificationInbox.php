<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PruneNotificationInbox extends Command
{
    protected $signature = 'notifications:prune-inbox {--days= : Retention period} {--dry-run}';

    protected $description = 'Prune old read or archived noncritical inbox projections without deleting audit records';

    public function handle(): int
    {
        foreach (['archived_at', 'category'] as $column) {
            if (!Schema::hasColumn('notifications', $column)) {
                $this->warn('Notification Center v2 schema is not installed; nothing was pruned.');
                return self::SUCCESS;
            }
        }

        $days = max(1, (int) ($this->option('days') ?: config('notifications_v2.retention_days', 90)));
        $critical = config('notifications_v2.critical_inbox_categories', []);
        $query = DB::table('notifications')
            ->where('created_at', '<', now()->subDays($days))
            ->where(function ($builder) {
                $builder->whereNotNull('read_at')->orWhereNotNull('archived_at');
            })
            ->where(function ($builder) use ($critical) {
                $builder->whereNull('category')->orWhereNotIn('category', $critical);
            });

        $count = (clone $query)->count();
        if ($this->option('dry-run')) {
            $this->info("{$count} inbox rows are eligible; no rows were deleted.");
            return self::SUCCESS;
        }

        $deleted = 0;
        do {
            $ids = (clone $query)->limit(500)->pluck('id');
            $batch = $ids->isEmpty()
                ? 0
                : DB::table('notifications')->whereIn('id', $ids)->delete();
            $deleted += $batch;
        } while ($batch === 500);

        $this->info("Pruned {$deleted} noncritical inbox rows. Audit events and delivery history were retained.");

        return self::SUCCESS;
    }
}

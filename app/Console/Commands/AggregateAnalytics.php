<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AggregateAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:aggregate {--days=7 : Number of days to aggregate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-aggregate daily analytics metrics into the summary table';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\AnalyticsService $analyticsService)
    {
        $days = $this->option('days');
        $this->info("Aggregating analytics for the last $days days...");

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->toDateString();
            $this->comment("Processing $date...");
            $analyticsService->aggregateSummary($date);
        }

        $this->info('Aggregation complete!');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\AggregateDailyAnalyticsJob;
use App\Jobs\AggregateMarketingMetricsJob;
use Carbon\Carbon;

class SimulateAnalyticsLoad extends Command
{
    protected $signature = 'analytics:simulate-load {count=200000}';
    protected $description = 'Simulate high-volume visitor data and measure aggregation performance.';

    public function handle()
    {
        $count = $this->argument('count');
        $this->info("Simulating $count visitor sessions...");

        $batchSize = 500;
        $batches = $count / $batchSize;

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $batches; $i++) {
            $data = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $sources = ['Google', 'Facebook', 'Direct', 'Instagram', 'Referral'];
                $data[] = [
                    'session_id' => bin2hex(random_bytes(16)),
                    'user_id' => rand(0, 1) ? rand(1, 1000) : null,
                    'ip_address' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'url' => '/test-page-' . rand(1, 100),
                    'method' => 'GET',
                    'referrer' => $sources[array_rand($sources)],
                    'is_entry' => rand(0, 1),
                    'is_exit' => rand(0, 1),
                    'time_spent' => rand(10, 300),
                    'created_at' => Carbon::yesterday()->addSeconds(rand(0, 86400)),
                    'updated_at' => Carbon::yesterday(),
                ];
            }
            DB::table('visitor_metrics')->insert($data);
            $bar->advance($batchSize);
        }

        $bar->finish();
        $this->info("\nData seeded. Starting aggregation benchmark...");

        $start = microtime(true);
        
        // Run the main aggregation jobs
        dispatch_sync(new AggregateDailyAnalyticsJob(Carbon::yesterday()->toDateString()));
        dispatch_sync(new AggregateMarketingMetricsJob(Carbon::yesterday()->toDateString()));
        
        $duration = microtime(true) - $start;

        $this->info("Aggregation completed in " . round($duration, 2) . " seconds.");
        $this->info("Performance: " . round($count / $duration) . " sessions/sec.");
    }
}

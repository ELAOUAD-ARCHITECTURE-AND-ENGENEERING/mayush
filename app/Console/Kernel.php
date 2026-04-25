<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('orders:cancel_unpaid')->daily();
        $schedule->command('orders:send_reminders')->hourly();
        $schedule->command('search:reindex')->daily();
        $schedule->command('promotions:expire')->daily();
        $schedule->command('elite:expire')->hourly();
        
        // MA-106: Predictive Restock Alerts
        $schedule->command('inventory:check-velocity')->daily();
        $schedule->command('inventory:update-affinities --threshold=2')->daily();
        $schedule->command('inventory:prune-affinities --days=30')->daily();

        // Stock alerts for users
        $schedule->command('stock:send-alerts')->hourly();

        // SEO: keep the public sitemap fresh for crawlers and AI answer engines
        $schedule->command('app:generate-sitemap')->dailyAt('02:30');

        // MA-099b: Vault token maintenance
        $schedule->command('vault:prune-expired')->dailyAt('02:00');

        // ONESSTA 3PL Shipping Integration
        $schedule->job(\Mayush\Shipping\Onessta\Jobs\SyncCitiesJob::class)->dailyAt('00:00');
        $schedule->job(\Mayush\Shipping\Onessta\Jobs\SyncPickupCitiesJob::class)->dailyAt('01:00');
        $schedule->job(\Mayush\Shipping\Onessta\Jobs\PollTrackingJob::class)->everyFiveMinutes();

        // Analytics Aggregation Jobs
        $schedule->job(\App\Jobs\AggregateDailyAnalyticsJob::class)->dailyAt('00:05');
        $schedule->job(\App\Jobs\AggregateVendorPerformanceJob::class)->dailyAt('00:10');
        $schedule->job(\App\Jobs\AggregateMarketingMetricsJob::class)->dailyAt('00:15');
        $schedule->job(\App\Jobs\AggregateSecurityMetricsJob::class)->dailyAt('00:20');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

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

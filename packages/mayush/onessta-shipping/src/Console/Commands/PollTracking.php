<?php

namespace Mayush\Shipping\Onessta\Console\Commands;

use Illuminate\Console\Command;
use Mayush\Shipping\Onessta\Jobs\PollTrackingJob;

class PollTracking extends Command
{
    protected $signature = 'onessta:poll-tracking';
    protected $description = 'Poll ONESSTA for tracking updates on all active shipments';

    public function handle(): int
    {
        $this->info('Queuing tracking poll job for all active shipments...');

        PollTrackingJob::dispatch();

        $this->info('Tracking poll job has been queued. Check logs for results.');

        return Command::SUCCESS;
    }
}

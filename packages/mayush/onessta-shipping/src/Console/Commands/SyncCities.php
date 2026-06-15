<?php

namespace Mayush\Shipping\Onessta\Console\Commands;

use Illuminate\Console\Command;
use Mayush\Shipping\Onessta\Jobs\SyncCitiesJob;

class SyncCities extends Command
{
    protected $signature = 'onessta:sync-cities {--force : Force sync even if cache is valid}';
    protected $description = 'Sync ONESSTA cities to local database';

    public function handle(): int
    {
        $force = $this->option('force');

        if ($force) {
            $this->info('Force syncing cities (cache will be ignored)...');
        }

        SyncCitiesJob::dispatch($force);

        $this->info('City sync job has been queued. Check logs for results.');

        return Command::SUCCESS;
    }
}

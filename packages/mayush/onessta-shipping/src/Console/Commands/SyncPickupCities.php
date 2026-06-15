<?php

namespace Mayush\Shipping\Onessta\Console\Commands;

use Illuminate\Console\Command;
use Mayush\Shipping\Onessta\Jobs\SyncPickupCitiesJob;

class SyncPickupCities extends Command
{
    protected $signature = 'onessta:sync-pickup-cities {--force : Force sync even if cache is valid}';
    protected $description = 'Sync ONESSTA pickup cities to local database';

    public function handle(): int
    {
        $force = $this->option('force');

        if ($force) {
            $this->info('Force syncing pickup cities (cache will be ignored)...');
        }

        SyncPickupCitiesJob::dispatch($force);

        $this->info('Pickup city sync job has been queued. Check logs for results.');

        return Command::SUCCESS;
    }
}

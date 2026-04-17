<?php

namespace Mayush\Shipping\Onessta\Console\Commands;

use Illuminate\Console\Command;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;

class FlushPickupCitiesCache extends Command
{
    protected $signature = 'onessta:cache:flush-pickup-cities';
    protected $description = 'Flush the ONESSTA pickup cities cache';

    public function __construct(
        private readonly ReferenceDataService $referenceDataService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->referenceDataService->flushPickupCitiesCache();
        $this->info('ONESSTA pickup cities cache flushed successfully.');

        return Command::SUCCESS;
    }
}

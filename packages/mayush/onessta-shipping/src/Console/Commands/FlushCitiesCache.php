<?php

namespace Mayush\Shipping\Onessta\Console\Commands;

use Illuminate\Console\Command;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;

class FlushCitiesCache extends Command
{
    protected $signature = 'onessta:cache:flush-cities';
    protected $description = 'Flush the ONESSTA cities cache';

    public function __construct(
        private readonly ReferenceDataService $referenceDataService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->referenceDataService->flushCitiesCache();
        $this->info('ONESSTA cities cache flushed successfully.');

        return Command::SUCCESS;
    }
}

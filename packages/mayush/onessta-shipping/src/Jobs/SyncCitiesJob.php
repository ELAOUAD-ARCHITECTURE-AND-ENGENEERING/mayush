<?php

namespace Mayush\Shipping\Onessta\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;

class SyncCitiesJob implements ShouldQueue
{
    public $tries = 2;
    public $timeout = 300;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $force = false;

    public function __construct(bool $force = false)
    {
        $this->onQueue('shipping');
        $this->force = $force;
        
    }

    public function handle(ReferenceDataService $referenceDataService): void
    {
        Log::info('ONESSTA: SyncCitiesJob started', ['force' => $this->force]);

        $cities = $referenceDataService->syncCities($this->force);

        Log::info('ONESSTA: SyncCitiesJob completed', ['count' => count($cities)]);
    }
}

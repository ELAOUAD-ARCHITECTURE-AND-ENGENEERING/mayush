<?php

namespace Mayush\Shipping\Onessta\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;

class SyncPickupCitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public bool $force = false;

    public function __construct(bool $force = false)
    {
        $this->force = $force;
        $this->queue = config('onessta.queue.name', 'onessta');
    }

    public function handle(ReferenceDataService $referenceDataService): void
    {
        Log::info('ONESSTA: SyncPickupCitiesJob started', ['force' => $this->force]);

        $cities = $referenceDataService->syncPickupCities($this->force);

        Log::info('ONESSTA: SyncPickupCitiesJob completed', ['count' => count($cities)]);
    }
}

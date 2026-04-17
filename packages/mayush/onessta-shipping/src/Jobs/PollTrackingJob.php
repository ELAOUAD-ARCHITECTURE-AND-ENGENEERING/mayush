<?php

namespace Mayush\Shipping\Onessta\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Services\TrackingService;

class PollTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct()
    {
        $this->queue = config('onessta.queue.name', 'onessta');
    }

    public function handle(TrackingService $trackingService): void
    {
        $maxParcels = config('onessta.polling.max_parcels_per_run', 100);

        $shipments = OnesstaShipment::undelivered()
            ->limit($maxParcels)
            ->get();

        Log::info('ONESSTA: PollTrackingJob started', ['count' => $shipments->count()]);

        foreach ($shipments as $shipment) {
            try {
                $trackingService->pollAndUpdate($shipment->code);
            } catch (\Throwable $e) {
                Log::warning('ONESSTA: Tracking poll failed for shipment', [
                    'code' => $shipment->code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('ONESSTA: PollTrackingJob completed');
    }
}

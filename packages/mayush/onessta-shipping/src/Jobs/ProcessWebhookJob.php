<?php

namespace Mayush\Shipping\Onessta\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Models\OnesstaWebhookLog;
use Mayush\Shipping\Onessta\Services\WebhookService;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly OnesstaWebhookLog $webhookLog
    ) {
        $this->queue = config('onessta.queue.name', 'onessta');
    }

    public function handle(WebhookService $webhookService): void
    {
        Log::info('ONESSTA: ProcessWebhookJob started', [
            'log_id' => $this->webhookLog->id,
            'event' => $this->webhookLog->event_type,
        ]);

        $webhookService->process($this->webhookLog);

        Log::info('ONESSTA: ProcessWebhookJob completed', ['log_id' => $this->webhookLog->id]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ONESSTA: ProcessWebhookJob permanently failed', [
            'log_id' => $this->webhookLog->id,
            'error' => $exception->getMessage(),
        ]);

        $this->webhookLog->markAsFailed($exception->getMessage());
    }
}

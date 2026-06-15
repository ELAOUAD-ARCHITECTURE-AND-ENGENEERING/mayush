<?php

namespace App\Jobs;

use App\Models\Product;
use App\Utility\SemanticUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSemanticEmbeddingJob implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 120;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    /**
     * The number of times the job may be attempted.
     * We want to retry in case the Gemini API throws rate limits or timeouts.
     */

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product)
    {
        $this->onQueue('embeddings');
        $this->product = $product;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            SemanticUtility::syncEmbedding($this->product);
        } catch (\Exception $e) {
            // Throwing triggers the automatic queue retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("SemanticEmbedding Sync failed for Product ID: {$this->product->id}. Error: " . $exception->getMessage());
        
        $slackWebhook = config('services.slack.security_webhook_url') ?? env('SLACK_SECURITY_WEBHOOK_URL');
        if (!empty($slackWebhook)) {
            try {
                \Illuminate\Support\Facades\Http::post($slackWebhook, [
                    'text' => "🚨 *Mayush Gemini API Critical Error*\nFailed to sync embedding for Product ID {$this->product->id} after 3 attempts.\n*Error:* `{$exception->getMessage()}`"
                ]);
            } catch (\Exception $e) {
                // Ignore slack fallback failure
            }
        }
    }
}

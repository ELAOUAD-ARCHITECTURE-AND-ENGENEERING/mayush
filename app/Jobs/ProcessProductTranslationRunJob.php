<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductTranslationRun;
use App\Models\ProductTranslationRunItem;
use App\Services\ProductTranslationRepairService;
use App\Services\ProductTranslationStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessProductTranslationRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout;

    public function __construct(public int $runId)
    {
        $this->timeout = max(60, (int) config('product_translation.worker_timeout', 480));
        $this->onQueue((string) config('product_translation.queue', 'translations'));
        $this->onConnection((string) config('product_translation.queue_connection', config('queue.default', 'sync')));
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('product-translation-run:'.$this->runId))
                ->releaseAfter(5)
                ->expireAfter(900),
        ];
    }

    public function handle(ProductTranslationRepairService $repairService): void
    {
        $run = ProductTranslationRun::find($this->runId);
        if (!$run || !$run->isActive() || $run->status === 'paused') {
            return;
        }

        $item = DB::transaction(function () use ($run) {
            $staleBefore = now()->subMinutes(15);
            $item = ProductTranslationRunItem::query()
                ->where('run_id', $run->id)
                ->where(function ($query) use ($staleBefore) {
                    $query->where('status', 'pending')
                        ->orWhere(function ($stale) use ($staleBefore) {
                            $stale->where('status', 'processing')->where('started_at', '<=', $staleBefore);
                        });
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->first();
            if (!$item) {
                return null;
            }
            $item->forceFill([
                'status' => 'processing',
                'attempt_count' => $item->attempt_count + 1,
                'started_at' => now(),
                'error_message' => null,
            ])->save();
            $run->forceFill(['status' => 'running', 'processing_product_id' => $item->product_id, 'failure_reason' => null, 'last_progress_at' => now()])->save();
            return $item;
        });

        if (!$item) {
            $this->finish($run);
            return;
        }

        $result = null;
        try {
            $product = Product::find($item->product_id);
            if (!$product) {
                $result = ['status' => 'skipped', 'error' => 'Product was deleted before processing.'];
            } else {
                $result = $repairService->repair($product);
            }
        } catch (Throwable $exception) {
            $result = ['status' => 'failed', 'error' => 'The product could not be translated safely.'];
        }

        $item->refresh();
        $errorCode = $result['error_code'] ?? null;
        $run->forceFill([
            'provider' => $result['provider'] ?? 'openrouter',
            'requested_model' => $result['requested_model'] ?? config('services.openrouter.model'),
            'actual_model' => $result['actual_model'] ?? null,
            'last_operation_id' => $result['operation_id'] ?? null,
            'last_request_duration_ms' => $result['request_duration_ms'] ?? null,
            'last_input_characters' => $result['input_characters'] ?? null,
            'last_prompt_tokens' => $result['usage']['prompt_tokens'] ?? null,
            'last_completion_tokens' => $result['usage']['completion_tokens'] ?? null,
            'last_total_tokens' => $result['usage']['total_tokens'] ?? null,
            'prompt_version' => \App\Services\OpenRouterProductTranslationPrompt::VERSION,
            'last_progress_at' => now(),
        ])->save();

        // A translation failure is terminal for the current background run.
        // This prevents failed products from silently continuing in the
        // background and leaves an explicit manual retry path in the UI.
        if (($result['status'] ?? 'failed') === 'failed') {
            $this->stopAfterFailure($run, $item, $result);
            return;
        }

        if ($errorCode === 'rate_limit') {
            $maxItemAttempts = max(1, (int) config('product_translation.max_item_attempts', 3));
            if ($item->attempt_count >= $maxItemAttempts) {
                $item->forceFill([
                    'status' => 'failed',
                    'error_message' => 'La limite temporaire du service de traduction a persisté après plusieurs tentatives.',
                    'completed_at' => now(),
                    'retry_decision' => 'stop_retry_limit',
                ])->save();
                $this->syncCounters($run);
                $run->forceFill([
                    'status' => 'paused',
                    'processing_product_id' => null,
                    'failure_reason' => 'La limite temporaire du service de traduction persiste. Le traitement est en pause; relancez les échecs manuellement.',
                    'last_retry_decision' => 'stop_retry_limit',
                    'last_progress_at' => now(),
                ])->save();
                return;
            }
            $item->forceFill([
                'status' => 'pending',
                'translated_field_count' => count($result['translated_fields'] ?? []),
                'translated_characters' => (int) ($result['translated_characters'] ?? 0),
                'error_message' => null,
                'completed_at' => null,
                'provider' => $result['provider'] ?? 'openrouter',
                'requested_model' => $result['requested_model'] ?? null,
                'actual_model' => $result['actual_model'] ?? null,
                'operation_id' => $result['operation_id'] ?? null,
                'request_duration_ms' => $result['request_duration_ms'] ?? null,
                'input_characters' => $result['input_characters'] ?? null,
                'prompt_version' => \App\Services\OpenRouterProductTranslationPrompt::VERSION,
                'retry_decision' => 'delayed',
                'translation_hash' => $result['translation_hash'] ?? null,
            ])->save();
            $this->syncCounters($run);
            $delay = (int) ($result['retry_after'] ?? config('product_translation.quota_retry_delay', 60));
            $run->forceFill([
                'status' => 'waiting_for_rate_limit',
                'processing_product_id' => null,
                'failure_reason' => 'La limite temporaire du service de traduction a été atteinte. Le traitement reprendra automatiquement.',
                'last_retry_decision' => 'delayed',
                'last_progress_at' => now(),
            ])->save();
            ProcessProductTranslationRunJob::dispatch($run->id)->delay(now()->addSeconds(max(1, min($delay, 3600))));
            return;
        }

        $isTemporary = in_array($errorCode, ['temporary_failure', 'timeout'], true);
        $maxItemAttempts = max(1, (int) config('product_translation.max_item_attempts', 3));
        if ($isTemporary && $item->attempt_count < $maxItemAttempts) {
            $delay = (int) config('product_translation.temporary_retry_delay', 15);
            $item->forceFill([
                'status' => 'pending',
                'error_message' => null,
                'completed_at' => null,
                'retry_decision' => 'delayed',
            ])->save();
            $run->forceFill([
                'status' => 'retrying',
                'processing_product_id' => null,
                'failure_reason' => 'Le service de traduction est temporairement indisponible. Nouvelle tentative automatique en cours.',
                'last_retry_decision' => 'delayed',
                'last_progress_at' => now(),
            ])->save();
            ProcessProductTranslationRunJob::dispatch($run->id)->delay(now()->addSeconds(max(1, min($delay, 3600))));
            return;
        }

        $item->forceFill([
            'status' => $result['status'] === 'success' ? 'succeeded' : ($result['status'] === 'skipped' ? 'skipped' : 'failed'),
            'translated_field_count' => count($result['translated_fields'] ?? []),
            'translated_characters' => (int) ($result['translated_characters'] ?? 0),
            'error_message' => $this->errorMessage($result),
            'completed_at' => now(),
            'provider' => $result['provider'] ?? 'openrouter',
            'requested_model' => $result['requested_model'] ?? null,
            'actual_model' => $result['actual_model'] ?? null,
            'operation_id' => $result['operation_id'] ?? null,
            'request_duration_ms' => $result['request_duration_ms'] ?? null,
            'input_characters' => $result['input_characters'] ?? null,
            'prompt_version' => \App\Services\OpenRouterProductTranslationPrompt::VERSION,
            'retry_decision' => (($result['status'] ?? 'failed') === 'failed' ? 'continue' : 'none'),
            'translation_hash' => $result['translation_hash'] ?? null,
        ])->save();

        $this->syncCounters($run);
        if (($result['status'] ?? 'failed') === 'failed') {
            $errorMessage = $this->errorMessage($result);
            if (in_array($errorCode, ['configuration', 'credentials', 'invalid_model', 'account_credit', 'structured_output_unsupported'], true)) {
                $run->forceFill([
                    'status' => 'failed',
                    'active_key' => null,
                    'processing_product_id' => null,
                    'failure_reason' => 'Le service de traduction automatique n’est pas disponible avec la configuration actuelle.',
                    'finished_at' => now(),
                    'last_retry_decision' => 'stop_permanent',
                    'last_progress_at' => now(),
                ])->save();
                return;
            }
            $run->forceFill([
                'status' => 'running',
                'processing_product_id' => null,
                'failure_reason' => $errorMessage ?: 'Un produit n’a pas pu être traduit; le traitement continue.',
                'last_retry_decision' => 'continue',
                'last_progress_at' => now(),
            ])->save();
            ProcessProductTranslationRunJob::dispatch($run->id);
            return;
        }

        $run->forceFill(['processing_product_id' => null, 'last_progress_at' => now()])->save();
        $run->refresh();
        if ($run->pending_count > 0) {
            ProcessProductTranslationRunJob::dispatch($run->id);
            return;
        }

        $this->finish($run);
    }

    public function failed(Throwable $exception): void
    {
        $run = ProductTranslationRun::find($this->runId);
        if ($run) {
            if ($run->processing_product_id) {
                ProductTranslationRunItem::query()
                    ->where('run_id', $run->id)
                    ->where('product_id', $run->processing_product_id)
                    ->where('status', 'processing')
                    ->update([
                        'status' => 'failed',
                        'error_message' => 'La file de traitement a interrompu ce produit.',
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
            $run->forceFill(['status' => 'failed', 'active_key' => null, 'failure_reason' => 'The queue stopped the translation run.', 'finished_at' => now(), 'last_progress_at' => now()])->save();
        }
    }

    private function syncCounters(ProductTranslationRun $run): void
    {
        $items = $run->items();
        $run->forceFill([
            'total_candidates' => (clone $items)->count(),
            'pending_count' => (clone $items)->where('status', 'pending')->count(),
            'processed_count' => (clone $items)->whereIn('status', ['succeeded', 'skipped', 'failed'])->count(),
            'success_count' => (clone $items)->where('status', 'succeeded')->count(),
            'skipped_count' => (clone $items)->where('status', 'skipped')->count(),
            'failed_count' => (clone $items)->where('status', 'failed')->count(),
            'translated_field_count' => (clone $items)->sum('translated_field_count'),
            'translated_characters' => (clone $items)->sum('translated_characters'),
            'last_progress_at' => now(),
        ])->save();
    }

    private function stopAfterFailure(ProductTranslationRun $run, ProductTranslationRunItem $item, array $result): void
    {
        $errorCode = (string) ($result['error_code'] ?? 'translation_failed');
        $rateLimited = $errorCode === 'rate_limit';
        $message = $this->errorMessage($result) ?: ($rateLimited
            ? 'The OpenRouter rate limit was reached. The run is stopped; retry the failed products manually.'
            : 'The OpenRouter translation failed. The run is stopped; retry the failed products manually.');

        $item->forceFill([
            'status' => 'failed',
            'translated_field_count' => count($result['translated_fields'] ?? []),
            'translated_characters' => (int) ($result['translated_characters'] ?? 0),
            'error_message' => $message,
            'completed_at' => now(),
            'provider' => $result['provider'] ?? 'openrouter',
            'requested_model' => $result['requested_model'] ?? null,
            'actual_model' => $result['actual_model'] ?? null,
            'operation_id' => $result['operation_id'] ?? null,
            'request_duration_ms' => $result['request_duration_ms'] ?? null,
            'input_characters' => $result['input_characters'] ?? null,
            'prompt_version' => \App\Services\OpenRouterProductTranslationPrompt::VERSION,
            'retry_decision' => $rateLimited ? 'stop_rate_limit' : 'stop_failure',
            'translation_hash' => $result['translation_hash'] ?? null,
        ])->save();
        $this->syncCounters($run);

        $run->forceFill([
            'status' => $rateLimited ? 'paused' : 'failed',
            'active_key' => $rateLimited ? $run->active_key : null,
            'processing_product_id' => null,
            'failure_reason' => $message,
            'finished_at' => $rateLimited ? null : now(),
            'last_retry_decision' => $rateLimited ? 'stop_rate_limit' : 'stop_failure',
            'last_progress_at' => now(),
        ])->save();
    }

    private function finish(ProductTranslationRun $run): void
    {
        $this->syncCounters($run);
        $run->refresh();
        if ($run->pending_count > 0 || $run->items()->where('status', 'processing')->exists()) {
            if ($run->pending_count > 0) {
                ProcessProductTranslationRunJob::dispatch($run->id);
            }
            return;
        }
        app(\App\Services\ProductTranslationRunFinalizer::class)->finish($run);
    }

    private function errorMessage(array $result): ?string
    {
        if (filled($result['error'] ?? null)) {
            return (string) $result['error'];
        }

        $errors = $result['errors'] ?? [];
        if (is_array($errors) && $errors !== []) {
            return collect($errors)->map(fn ($value, $field) => $field.': '.$value)->implode('; ');
        }

        return filled($result['error_code'] ?? null) ? (string) $result['error_code'] : null;
    }
}

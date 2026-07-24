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
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessProductTranslationRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 180;

    public function __construct(public int $runId)
    {
        $this->onQueue((string) config('product_translation.queue', 'translations'));
    }

    public function handle(ProductTranslationRepairService $repairService): void
    {
        $run = ProductTranslationRun::find($this->runId);
        if (!$run || !$run->isActive() || $run->status === 'paused') {
            return;
        }

        $item = DB::transaction(function () use ($run) {
            $item = ProductTranslationRunItem::query()
                ->where('run_id', $run->id)
                ->where('status', 'pending')
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
            $run->forceFill(['status' => 'running', 'processing_product_id' => $item->product_id, 'last_progress_at' => now()])->save();
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
        $item->forceFill([
            'status' => $result['status'] === 'success' ? 'succeeded' : ($result['status'] === 'skipped' ? 'skipped' : 'failed'),
            'translated_field_count' => count($result['translated_fields'] ?? []),
            'azure_characters' => (int) ($result['azure_characters'] ?? 0),
            'error_message' => $this->errorMessage($result),
            'completed_at' => now(),
        ])->save();

        $this->syncCounters($run);
        if (($result['status'] ?? 'failed') === 'failed') {
            $errorMessage = $this->errorMessage($result);
            $failureReason = ($result['error_code'] ?? null) === 'rate_limit'
                ? 'Azure rate limit reached. The run stopped completely; retry the failed products when the quota is available.'
                : ($errorMessage ?: 'A product failed to translate. The run stopped completely.');

            $run->forceFill([
                'status' => 'failed',
                'active_key' => null,
                'processing_product_id' => null,
                'failure_reason' => $failureReason,
                'finished_at' => now(),
                'last_progress_at' => now(),
            ])->save();
            return;
        }

        $run->forceFill(['processing_product_id' => null, 'last_progress_at' => now()])->save();
        ProcessProductTranslationRunJob::dispatch($run->id);
    }

    public function failed(Throwable $exception): void
    {
        $run = ProductTranslationRun::find($this->runId);
        if ($run) {
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
            'azure_characters' => (clone $items)->sum('azure_characters'),
            'last_progress_at' => now(),
        ])->save();
    }

    private function finish(ProductTranslationRun $run): void
    {
        $this->syncCounters($run);
        $run->refresh();
        if ($run->pending_count > 0) {
            ProcessProductTranslationRunJob::dispatch($run->id);
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

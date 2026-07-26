<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductTranslationRun;
use App\Models\ProductTranslationRunItem;
use App\Services\ProductTranslationStatusService;
use App\Services\ProductTranslationRunFinalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class PrepareProductTranslationRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(public int $runId)
    {
        $this->onQueue((string) config('product_translation.queue', 'translations'));
        $this->onConnection((string) config('product_translation.queue_connection', config('queue.default', 'sync')));
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('product-translation-prepare:'.$this->runId))
                ->releaseAfter(5)
                ->expireAfter(900),
        ];
    }

    public function handle(ProductTranslationStatusService $statusService, ProductTranslationRunFinalizer $finalizer): void
    {
        $run = ProductTranslationRun::find($this->runId);
        if (!$run || !$run->isActive() || !in_array($run->status, ['queued', 'running'], true)) {
            return;
        }

        $run->forceFill([
            'status' => 'running',
            'started_at' => $run->started_at ?: now(),
            'last_progress_at' => now(),
        ])->save();

        Product::query()
            ->without(['taxes', 'thumbnail'])
            ->select(['id', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'draft'])
            ->where('draft', 0)
            ->with(['product_translations' => function ($query) use ($statusService) {
                $query->select(['id', 'product_id', 'lang', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'])
                    ->whereIn('lang', [$statusService->sourceLanguage(), $statusService->targetLanguage()]);
            }])
            ->orderBy('id')
            ->chunkById((int) config('product_translation.chunk_size', 100), function ($products) use ($run, $statusService) {
                $rows = [];
                foreach ($products as $product) {
                    $diagnosis = $statusService->diagnose($product);
                    if ($diagnosis['status'] === ProductTranslationStatusService::COMPLETE) {
                        continue;
                    }
                    $sourceMissing = $diagnosis['source_missing_fields'] !== [];
                    $rows[] = [
                        'run_id' => $run->id,
                        'product_id' => $product->id,
                        'status' => $sourceMissing ? 'skipped' : 'pending',
                        'missing_fields' => json_encode($diagnosis['missing_fields'], JSON_UNESCAPED_UNICODE),
                        'source_missing_fields' => json_encode($diagnosis['source_missing_fields'], JSON_UNESCAPED_UNICODE),
                        'completed_at' => $sourceMissing ? now() : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if ($rows !== []) {
                    ProductTranslationRunItem::query()->upsert(
                        $rows,
                        ['run_id', 'product_id'],
                        ['missing_fields', 'source_missing_fields', 'updated_at']
                    );
                }
                $this->syncCounters($run);
            });

        $this->syncCounters($run);
        $run->refresh();
        if ($run->pending_count > 0) {
            ProcessProductTranslationRunJob::dispatch($run->id);
            return;
        }

        $finalizer->finish($run);
    }

    public function failed(Throwable $exception): void
    {
        $run = ProductTranslationRun::find($this->runId);
        if ($run) {
            $run->forceFill([
                'status' => 'failed',
                'active_key' => null,
                'failure_reason' => 'The translation run could not be prepared.',
                'finished_at' => now(),
                'last_progress_at' => now(),
            ])->save();
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

    private function complete(ProductTranslationRun $run): void
    {
        $run->refresh();
        $run->forceFill([
            'status' => $run->failed_count > 0 ? 'completed_with_errors' : 'completed',
            'active_key' => null,
            'finished_at' => now(),
            'last_progress_at' => now(),
        ])->save();
        $this->notify($run);
    }

    private function notify(ProductTranslationRun $run): void
    {
        if (!$run->user_id || !config('notifications_v2.enabled')) {
            return;
        }
        app(\App\Services\Notifications\NotificationDispatcher::class)->dispatch(
            'product.translation_completed',
            'product_translation_run',
            $run->id,
            $run->failed_count > 0 ? 'completed_with_errors' : 'completed',
            [$run->user_id],
            [
                'title' => 'Correction des traductions terminée',
                'message' => sprintf('%d produits corrigés, %d ignorés et %d en erreur.', $run->success_count, $run->skipped_count, $run->failed_count),
                'action_url' => route('admin.product_translation_diagnostics', [], false),
            ]
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\PrepareProductTranslationRunJob;
use App\Jobs\ProcessProductTranslationRunJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslationRun;
use App\Models\ProductTranslationRunItem;
use App\Models\User;
use App\Services\ProductTranslationRepairService;
use App\Services\ProductTranslationRateLimitGuard;
use App\Services\ProductTranslationStatusService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductTranslationDiagnosticsController extends Controller
{
    public function __construct(
        private readonly ProductTranslationStatusService $statusService,
        private readonly ProductTranslationRepairService $repairService,
        private readonly ProductTranslationRateLimitGuard $rateLimitGuard
    ) {
        $this->middleware('permission:product_edit|show_all_products');
    }

    public function index(Request $request)
    {
        $summary = Cache::remember('admin:product-translation:summary', 30, fn () => $this->summaryData());
        $query = Product::query()
            ->without(['taxes', 'thumbnail'])
            ->select(['id', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'thumbnail_img', 'brand_id', 'user_id', 'category_id', 'draft', 'added_by'])
            ->where('draft', 0)
            ->with(['product_translations' => fn ($builder) => $builder
                ->select(['id', 'product_id', 'lang', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'])
                ->whereIn('lang', [$this->statusService->sourceLanguage(), $this->statusService->targetLanguage()])]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('id', is_numeric($search) ? (int) $search : 0)
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('product_translations', fn ($translation) => $translation->whereIn('lang', [$this->statusService->sourceLanguage(), $this->statusService->targetLanguage()])->where(function ($name) use ($search) {
                        $name->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                    }));
            });
        }
        foreach (['brand_id', 'category_id', 'user_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->integer($filter));
            }
        }

        $statusFilter = $request->input('status');
        $products = $statusFilter
            ? $query->latest('id')->get()
            : $query->latest('id')->paginate(20)->withQueryString();
        $productCollection = $products instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $products->getCollection()
            : $products;
        $failedItems = ProductTranslationRunItem::query()
            ->whereIn('product_id', $productCollection->pluck('id'))
            ->where('status', 'failed')
            ->latest('id')
            ->get()
            ->groupBy('product_id');
        $diagnoses = [];
        $filteredProducts = collect();
        foreach ($productCollection as $product) {
            $diagnosis = $this->diagnosisWithFailure($product, $failedItems->get($product->id)?->first());
            if ($statusFilter && $statusFilter !== $diagnosis['status']) {
                continue;
            }
            $filteredProducts->push($product);
            $diagnoses[$product->id] = $diagnosis;
        }
        if ($statusFilter) {
            $page = (int) $request->input('page', 1);
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                $filteredProducts->forPage($page, 20)->values(),
                $filteredProducts->count(),
                20,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('backend.product.translation_diagnostics.index', [
            'summary' => $summary,
            'products' => $products,
            'diagnoses' => $diagnoses,
            'activeRun' => ProductTranslationRun::query()->whereNotNull('active_key')->latest('id')->first(),
            'brands' => Brand::query()->select(['id', 'name'])->orderBy('name')->get(),
            'categories' => Category::query()->select(['id', 'name'])->where('parent_id', 0)->orderBy('name')->get(),
            'sellers' => User::query()->select(['id', 'name'])->where('user_type', 'seller')->orderBy('name')->get(),
            'statusOptions' => [
                ProductTranslationStatusService::COMPLETE => 'Traduction complète',
                ProductTranslationStatusService::PARTIAL => 'Traduction partielle',
                ProductTranslationStatusService::MISSING_ARABIC => 'Arabe manquant',
                ProductTranslationStatusService::CONTAINS_FRENCH_IN_ARABIC => 'Français présent dans l’arabe',
                ProductTranslationStatusService::MISSING_FRENCH_SOURCE => 'Source française incomplète',
                ProductTranslationStatusService::FAILED => 'Échec de traduction',
            ],
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json(['summary' => Cache::remember('admin:product-translation:summary', 30, fn () => $this->summaryData())]);
    }

    public function preview(): JsonResponse
    {
        $summary = Cache::remember('admin:product-translation:summary', 30, fn () => $this->summaryData());
        return response()->json([
            'summary' => $summary,
            'processable' => max(0, $summary['total'] - $summary[ProductTranslationStatusService::COMPLETE] - $summary[ProductTranslationStatusService::MISSING_FRENCH_SOURCE]),
            'estimated_fields' => $this->estimateFields(),
            'estimated_characters' => $this->estimateCharacters(),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $request->validate(['retry_failed_run_id' => ['nullable', 'integer', 'exists:product_translation_runs,id']]);
        if ($queueError = $this->backgroundQueueError()) {
            return $queueError;
        }

        $active = ProductTranslationRun::query()->whereNotNull('active_key')->whereIn('status', ['queued', 'running', 'paused', 'retrying', 'waiting_for_quota', 'waiting_for_rate_limit'])->latest('id')->first();
        if ($active) {
            if ($active->status === 'paused') {
                return response()->json([
                    'message' => 'Cette exécution est en pause. Utilisez « Réessayer les échecs » pour reprendre manuellement.',
                    'run' => $this->runPayload($active),
                ]);
            }

            return response()->json(['message' => 'Une traduction automatique est déjà en cours.', 'run' => $this->runPayload($active)], 409);
        }

        try {
            $run = DB::transaction(function () use ($request) {
                $run = ProductTranslationRun::create([
                    'user_id' => auth()->id(),
                    'active_key' => 'global',
                    'status' => 'queued',
                    'last_progress_at' => now(),
                ]);
                if ($request->filled('retry_failed_run_id')) {
                    $failedIds = ProductTranslationRunItem::query()->where('run_id', $request->integer('retry_failed_run_id'))->where('status', 'failed')->pluck('product_id');
                    $this->seedItems($run, $failedIds->all());
                    $run->forceFill(['total_candidates' => $failedIds->count(), 'pending_count' => $failedIds->count()])->save();
                }
                return $run;
            });
        } catch (QueryException) {
            $active = ProductTranslationRun::query()->whereNotNull('active_key')->latest('id')->first();
            return response()->json(['message' => 'Une traduction automatique est déjà en cours.', 'run' => $active ? $this->runPayload($active) : null], 409);
        }

        if ($request->filled('retry_failed_run_id')) {
            ProcessProductTranslationRunJob::dispatch($run->id);
        } else {
            PrepareProductTranslationRunJob::dispatch($run->id);
        }

        return response()->json(['run' => $this->runPayload($run), 'message' => 'La correction automatique a été mise en file d’attente.']);
    }

    public function progress(ProductTranslationRun $run): JsonResponse
    {
        $this->authorizeRun($run);
        return response()->json(['run' => $this->runPayload($run)]);
    }

    public function repair(Product $product): JsonResponse
    {
        if (($retryAfter = $this->rateLimitGuard->retryAfter()) > 0) {
            return $this->rateLimitResponse($retryAfter);
        }

        $lock = $this->rateLimitGuard->requestLock();
        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Une autre traduction est deja en cours. Reessayez dans quelques secondes.',
            ], 429)->header('Retry-After', '5');
        }

        try {
            $result = $this->repairService->repair($product);
        } finally {
            $lock->release();
        }
        if (($result['error_code'] ?? null) === 'rate_limit') {
            $retryAfter = $this->rateLimitGuard->block($result['retry_after'] ?? null);
        }
        Cache::forget('admin:product-translation:summary');
        $statusCode = match ($result['error_code'] ?? null) {
            'configuration', 'credentials', 'invalid_model', 'account_credit', 'structured_output_unsupported', 'temporary_failure', 'timeout' => 503,
            'rate_limit' => 429,
            'payload_too_large' => 413,
            'safety_blocked', 'incomplete_response', 'malformed_response', 'empty_response', 'request_failed' => 422,
            default => ($result['status'] === 'failed' ? 422 : 200),
        };

        $response = response()->json([
            'success' => $result['status'] === 'success',
            'result' => $result,
            'message' => $result['status'] === 'success' ? 'La version arabe a été corrigée.' : 'Aucune correction complète n’a été enregistrée.',
        ], $statusCode);

        if (($result['error_code'] ?? null) === 'rate_limit') {
            $response->header('Retry-After', (string) $retryAfter);
        }

        return $response;
    }

    private function rateLimitResponse(int $retryAfter): JsonResponse
    {
        return response()->json([
            'success' => false,
            'result' => [
                'status' => 'failed',
                'error_code' => 'rate_limit',
                'retry_after' => $retryAfter,
            ],
            'message' => 'La limite temporaire du service de traduction est active. Reessayez apres le delai indique.',
        ], 429)->header('Retry-After', (string) max(1, $retryAfter));
    }

    public function retryFailed(ProductTranslationRun $run, Request $request): JsonResponse
    {
        $this->authorizeRun($run);
        if ($queueError = $this->backgroundQueueError()) {
            return $queueError;
        }

        abort_unless(in_array($run->status, ['completed_with_errors', 'failed', 'paused', 'retrying', 'waiting_for_quota', 'waiting_for_rate_limit'], true), 422, 'Cette exécution ne contient pas de produits à relancer.');
        if ($run->next_retry_at && now()->lt($run->next_retry_at)) {
            $retryAfter = max(1, now()->diffInSeconds($run->next_retry_at));
            return response()->json([
                'message' => 'La limite du service de traduction est toujours active. Réessayez après le délai indiqué.',
                'run' => $this->runPayload($run),
            ], 429)->header('Retry-After', (string) $retryAfter);
        }
        if (in_array($run->status, ['retrying', 'waiting_for_quota', 'waiting_for_rate_limit'], true)) {
            $run->forceFill([
                'status' => 'queued',
                'failure_reason' => null,
                'processing_product_id' => null,
                'finished_at' => null,
                'next_retry_at' => null,
                'last_progress_at' => now(),
            ])->save();
            ProcessProductTranslationRunJob::dispatch($run->id);
            $fresh = $run->fresh();
            return response()->json(['run' => $fresh ? $this->runPayload($fresh) : null, 'message' => 'Le traitement va reprendre.']);
        }
        if ($run->status === 'paused') {
            DB::transaction(function () use ($run) {
                $run->items()->where('status', 'failed')->update([
                    'status' => 'pending',
                    'attempt_count' => 0,
                    'error_message' => null,
                    'completed_at' => null,
                    'updated_at' => now(),
                ]);
                $this->syncRunCounters($run);
                $run->forceFill([
                    'status' => 'queued',
                    'failure_reason' => null,
                    'processing_product_id' => null,
                    'finished_at' => null,
                    'next_retry_at' => null,
                    'last_progress_at' => now(),
                ])->save();
            });
            ProcessProductTranslationRunJob::dispatch($run->id);
            return response()->json(['run' => $this->runPayload($run->fresh()), 'message' => 'Les produits en échec vont être relancés.']);
        }

        $request->merge(['retry_failed_run_id' => $run->id]);
        return $this->start($request);
    }

    private function authorizeRun(ProductTranslationRun $run): void
    {
        abort_unless(auth()->user()->can('product_edit') || auth()->user()->can('show_all_products'), 403);
    }

    private function backgroundQueueError(): ?JsonResponse
    {
        if (config('product_translation.queue_connection', config('queue.default')) !== 'sync' || app()->runningUnitTests()) {
            return null;
        }

        return response()->json([
            'message' => 'La correction en arrière-plan nécessite une file asynchrone et un worker actif. Configurez PRODUCT_TRANSLATION_QUEUE_CONNECTION sur redis_translations.',
        ], 503);
    }

    private function summaryData(): array
    {
        $counts = array_fill_keys([
            ProductTranslationStatusService::COMPLETE,
            ProductTranslationStatusService::PARTIAL,
            ProductTranslationStatusService::MISSING_ARABIC,
            ProductTranslationStatusService::MISSING_FRENCH_SOURCE,
            ProductTranslationStatusService::CONTAINS_FRENCH_IN_ARABIC,
            ProductTranslationStatusService::FAILED,
        ], 0);
        $total = 0;
        $failedIds = ProductTranslationRunItem::query()->where('status', 'failed')->pluck('product_id')->flip();

        Product::query()->without(['taxes', 'thumbnail'])->select(['id', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'draft'])->where('draft', 0)
            ->with(['product_translations' => fn ($builder) => $builder->select(['id', 'product_id', 'lang', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'])->whereIn('lang', [$this->statusService->sourceLanguage(), $this->statusService->targetLanguage()])])
            ->orderBy('id')->chunkById((int) config('product_translation.chunk_size', 100), function ($products) use (&$counts, &$total, $failedIds) {
            foreach ($products as $product) {
                $total++;
                    $diagnosis = $this->statusService->diagnose($product);
                    $status = $failedIds->has($product->id) && $diagnosis['status'] !== ProductTranslationStatusService::COMPLETE
                        ? ProductTranslationStatusService::FAILED
                        : $diagnosis['status'];
                    $counts[$status]++;
                }
            });

        return array_merge(['total' => $total], $counts, ['percentages' => collect($counts)->map(fn ($value) => $total > 0 ? round(($value / $total) * 100, 1) : 0)->all()]);
    }

    private function seedItems(ProductTranslationRun $run, array $productIds): void
    {
        if ($productIds === []) {
            return;
        }
        Product::query()->without(['taxes', 'thumbnail'])->whereIn('id', $productIds)->with(['product_translations' => fn ($builder) => $builder->select(['id', 'product_id', 'lang', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'])->whereIn('lang', [$this->statusService->sourceLanguage(), $this->statusService->targetLanguage()])])->get()->each(function ($product) use ($run) {
            $diagnosis = $this->statusService->diagnose($product);
            ProductTranslationRunItem::create([
                'run_id' => $run->id,
                'product_id' => $product->id,
                'status' => $diagnosis['source_missing_fields'] === [] ? 'pending' : 'skipped',
                'missing_fields' => $diagnosis['missing_fields'],
                'source_missing_fields' => $diagnosis['source_missing_fields'],
                'completed_at' => $diagnosis['source_missing_fields'] === [] ? null : now(),
            ]);
        });
    }

    private function diagnosisWithFailure(Product $product, ?ProductTranslationRunItem $failure): array
    {
        $diagnosis = $this->statusService->diagnose($product);
        if ($failure && $diagnosis['status'] !== ProductTranslationStatusService::COMPLETE) {
            $diagnosis['status'] = ProductTranslationStatusService::FAILED;
            $diagnosis['last_error'] = $failure->error_message;
            $diagnosis['last_attempt_at'] = optional($failure->completed_at)->toIso8601String();
        }
        return $diagnosis;
    }

    private function runPayload(ProductTranslationRun $run): array
    {
        $current = $run->processing_product_id ? Product::query()->without(['taxes', 'thumbnail'])->find($run->processing_product_id) : null;
        return [
            'id' => $run->id,
            'status' => $run->status,
            'total' => (int) $run->total_candidates,
            'pending' => (int) $run->pending_count,
            'processed' => (int) $run->processed_count,
            'success' => (int) $run->success_count,
            'skipped' => (int) $run->skipped_count,
            'failed' => (int) $run->failed_count,
            'translated_fields' => (int) $run->translated_field_count,
            'translated_characters' => (int) $run->translated_characters,
            'provider' => $run->provider,
            'requested_model' => $run->requested_model,
            'actual_model' => $run->actual_model,
            'last_operation_id' => $run->last_operation_id,
            'last_request_duration_ms' => $run->last_request_duration_ms !== null ? (int) $run->last_request_duration_ms : null,
            'last_input_characters' => $run->last_input_characters !== null ? (int) $run->last_input_characters : null,
            'last_total_tokens' => $run->last_total_tokens !== null ? (int) $run->last_total_tokens : null,
            'last_retry_decision' => $run->last_retry_decision,
            'next_retry_at' => optional($run->next_retry_at)->toIso8601String(),
            'percentage' => $run->total_candidates > 0 ? round(($run->processed_count / $run->total_candidates) * 100, 1) : 0,
            'current_product' => $current ? ['id' => $current->id, 'name' => $current->name, 'thumbnail' => uploaded_asset($current->thumbnail_img)] : null,
            'started_at' => optional($run->started_at)->toIso8601String(),
            'finished_at' => optional($run->finished_at)->toIso8601String(),
            'last_progress_at' => optional($run->last_progress_at)->toIso8601String(),
            'failure_reason' => $this->displayFailureReason($run->failure_reason),
        ];
    }

    private function displayFailureReason(?string $reason): ?string
    {
        if ($reason === null || $reason === '') {
            return $reason;
        }

        if (stripos($reason, 'azure') !== false || stripos($reason, 'gemini') !== false) {
            return 'Cette exécution a été créée avec un ancien fournisseur. Relancez les échecs pour utiliser le service actuel.';
        }

        return $reason;
    }

    private function syncRunCounters(ProductTranslationRun $run): void
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

    private function estimateFields(): int
    {
        $summary = $this->summaryData();
        return ($summary[ProductTranslationStatusService::PARTIAL] + $summary[ProductTranslationStatusService::MISSING_ARABIC] + $summary[ProductTranslationStatusService::CONTAINS_FRENCH_IN_ARABIC]) * count($this->statusService->fields());
    }

    private function estimateCharacters(): int
    {
        return 0;
    }
}

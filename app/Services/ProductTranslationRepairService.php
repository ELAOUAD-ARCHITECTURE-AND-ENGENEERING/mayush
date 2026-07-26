<?php

namespace App\Services;

use App\Contracts\ProductTranslationService;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductTranslationRepairService
{
    public function __construct(
        private readonly ProductTranslationStatusService $statusService,
        private readonly ProductTranslationService $translationService
    ) {
    }

    public function repair(Product $product): array
    {
        $product->load('product_translations');
        $before = $this->statusService->diagnose($product);
        if ($before['status'] === ProductTranslationStatusService::COMPLETE) {
            return ['status' => 'skipped', 'diagnosis' => $before, 'translated_fields' => [], 'translated_characters' => 0];
        }
        if ($before['source_missing_fields'] !== []) {
            return ['status' => 'skipped', 'diagnosis' => $before, 'translated_fields' => [], 'translated_characters' => 0];
        }

        $source = $this->statusService->sourceValues($product);
        if ($source === []) {
            return ['status' => 'skipped', 'diagnosis' => $before, 'translated_fields' => [], 'translated_characters' => 0];
        }

        $sourceLanguage = $this->statusService->sourceLanguage();
        $targetLanguage = (string) config('product_translation.translation_target_language', 'ar');
        $translationHash = $this->translationHash($product, $source, $sourceLanguage, $targetLanguage);
        $cacheKey = 'product-translation:'.$translationHash;
        $cachedFields = Cache::get($cacheKey);
        if (is_array($cachedFields) && $cachedFields !== []) {
            $result = [
                'success' => true,
                'fields' => $cachedFields,
                'failed_fields' => [],
                'errors' => [],
                'translated_count' => count($cachedFields),
                'from_cache' => true,
            ];
        } else {
            $result = $this->translationService->translateFields($source, $sourceLanguage, $targetLanguage);
            if (($result['success'] ?? false) && ($result['failed_fields'] ?? []) === []) {
                Cache::put($cacheKey, $result['fields'], now()->addSeconds((int) config('product_translation.cache_ttl', 2592000)));
            }
        }
        $translatedFields = $this->normalizeUnitTranslation($source, $result['fields'] ?? []);
        $translated = collect($translatedFields)
            ->filter(fn ($value, $field) => array_key_exists($field, $source) && is_string($value) && trim($value) !== '' && !in_array($field, $result['failed_fields'] ?? [], true))
            ->all();
        $failed = $result['failed_fields'] ?? [];
        if (
            array_key_exists('unit', $translatedFields)
            && array_key_exists('unit', $result['fields'] ?? [])
            && $translatedFields['unit'] !== $result['fields']['unit']
        ) {
            // Common unit labels have a deterministic Arabic equivalent. Do
            // not let a provider timeout on this tiny field prevent a product
            // from completing after the safe fallback has been applied.
            $failed = array_values(array_diff($failed, ['unit']));
            $translated['unit'] = $translatedFields['unit'];
        }
        $characters = collect($translated)->sum(fn (string $value) => mb_strlen(strip_tags($value)));

        if ($translated !== []) {
            DB::transaction(function () use ($product, $translated) {
                $locked = Product::query()->lockForUpdate()->find($product->id);
                if (!$locked) {
                    return;
                }
                $locked->load('product_translations');
                $current = $this->statusService->diagnose($locked);
                $row = ProductTranslation::firstOrNew([
                    'product_id' => $locked->id,
                    'lang' => $this->statusService->targetLanguage(),
                ]);
                foreach ($translated as $field => $value) {
                    if (($current['fields'][$field]['target_state'] ?? 'missing') !== 'valid') {
                        $row->{$field} = $value;
                    }
                }
                $row->save();
            });
        }

        $product->load('product_translations');
        $after = $this->statusService->diagnose($product);

        return [
            'status' => $failed === [] && $after['status'] === ProductTranslationStatusService::COMPLETE ? 'success' : 'failed',
            'diagnosis' => $after,
            'translated_fields' => array_keys($translated),
            'failed_fields' => $failed,
            'error_code' => $result['error_code'] ?? null,
            'errors' => $result['errors'] ?? [],
            'translated_characters' => $characters,
            'translation_hash' => $translationHash,
            'provider' => $result['provider'] ?? config('product_translation.provider', 'openrouter'),
            'requested_model' => $result['requested_model'] ?? config('services.openrouter.model'),
            'actual_model' => $result['actual_model'] ?? null,
            'operation_id' => $result['operation_id'] ?? null,
            'attempt' => $result['attempt'] ?? 0,
            'request_duration_ms' => $result['request_duration_ms'] ?? null,
            'input_characters' => $result['input_characters'] ?? null,
            'usage' => $result['usage'] ?? [],
            'retry_after' => $result['retry_after'] ?? null,
        ];
    }

    private function translationHash(Product $product, array $source, string $sourceLanguage, string $targetLanguage): string
    {
        return hash('sha256', json_encode([
            'product_id' => $product->id,
            'fields' => $source,
            'source_language' => $sourceLanguage,
            'target_language' => $targetLanguage,
            'prompt_version' => \App\Services\OpenRouterProductTranslationPrompt::VERSION,
            'requested_model' => config('services.openrouter.model'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function normalizeUnitTranslation(array $source, array $translated): array
    {
        if (!isset($source['unit'], $translated['unit']) || !is_string($source['unit']) || !is_string($translated['unit'])) {
            return $translated;
        }

        $sourceUnit = mb_strtolower(trim(strip_tags($source['unit'])));
        $targetUnit = mb_strtolower(trim(strip_tags($translated['unit'])));
        if ($sourceUnit === '' || $sourceUnit !== $targetUnit) {
            return $translated;
        }

        $standardUnits = [
            'pc' => 'قطعة',
            'pcs' => 'قطع',
            'piece' => 'قطعة',
            'pieces' => 'قطع',
            'set' => 'طقم',
            'sets' => 'أطقم',
            'unité' => 'وحدة',
            'unit' => 'وحدة',
        ];

        if (isset($standardUnits[$sourceUnit])) {
            $translated['unit'] = $standardUnits[$sourceUnit];
        }

        return $translated;
    }
}

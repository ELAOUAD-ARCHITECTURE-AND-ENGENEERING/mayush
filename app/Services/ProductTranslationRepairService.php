<?php

namespace App\Services;

use App\Contracts\ProductTranslationService;
use App\Models\Product;
use App\Models\ProductTranslation;
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
            return ['status' => 'skipped', 'diagnosis' => $before, 'translated_fields' => [], 'azure_characters' => 0];
        }
        if ($before['source_missing_fields'] !== []) {
            return ['status' => 'skipped', 'diagnosis' => $before, 'translated_fields' => [], 'azure_characters' => 0];
        }

        $source = collect($before['fields'])
            ->filter(fn (array $field) => $field['source_present'] && $field['target_state'] !== 'valid')
            ->mapWithKeys(fn (array $field, string $name) => [$name => $field['source']])
            ->all();
        if ($source === []) {
            return ['status' => 'skipped', 'diagnosis' => $before, 'translated_fields' => [], 'azure_characters' => 0];
        }

        $result = $this->translationService->translateFields(
            $source,
            $this->statusService->sourceLanguage(),
            (string) config('product_translation.azure_target_language', 'ar')
        );
        $translated = collect($result['fields'] ?? [])
            ->filter(fn ($value, $field) => array_key_exists($field, $source) && is_string($value) && trim($value) !== '' && !in_array($field, $result['failed_fields'] ?? [], true))
            ->all();
        $failed = $result['failed_fields'] ?? [];
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
            'azure_characters' => $characters,
        ];
    }
}

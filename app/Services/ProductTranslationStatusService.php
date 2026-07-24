<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductTranslationStatusService
{
    public const COMPLETE = 'complete';
    public const PARTIAL = 'partial';
    public const MISSING_ARABIC = 'missing_arabic';
    public const MISSING_FRENCH_SOURCE = 'missing_french_source';
    public const CONTAINS_FRENCH_IN_ARABIC = 'contains_french_in_arabic';
    public const FAILED = 'failed';

    public function fields(): array
    {
        return config('product_translation.fields', ['name', 'unit', 'description']);
    }

    public function requiredFields(): array
    {
        return config('product_translation.required_fields', ['name']);
    }

    public function sourceLanguage(): string
    {
        return (string) config('product_translation.source_language', 'fr');
    }

    public function targetLanguage(): string
    {
        $configured = (string) config('product_translation.target_language', 'ma');
        if ($configured !== '') {
            return $configured;
        }

        try {
            return (string) (Language::query()->where('status', 1)->where('rtl', 1)->value('code') ?: 'ma');
        } catch (\Throwable) {
            return 'ma';
        }
    }

    public function diagnose(Product $product): array
    {
        $translations = $product->relationLoaded('product_translations')
            ? $product->product_translations
            : $product->product_translations()->whereIn('lang', [$this->sourceLanguage(), $this->targetLanguage()])->get();
        $source = $translations->firstWhere('lang', $this->sourceLanguage());
        $target = $translations->firstWhere('lang', $this->targetLanguage());
        $fields = [];
        $missing = [];
        $sourceMissing = [];
        $untranslated = [];
        $valid = [];
        $expected = [];

        foreach ($this->fields() as $field) {
            $sourceValue = $this->sourceValue($product, $source, $field);
            $targetValue = trim((string) ($target?->{$field} ?? ''));
            $sourcePresent = trim(strip_tags((string) $sourceValue)) !== '';
            $required = in_array($field, $this->requiredFields(), true);
            $targetState = $this->targetState($field, $sourceValue, $targetValue);

            if ($required || $sourcePresent || $targetValue !== '') {
                $expected[] = $field;
            }
            if (!$sourcePresent && $required) {
                $sourceMissing[] = $field;
            } elseif (!$sourcePresent && $targetValue === '') {
                continue;
            }

            $fields[$field] = [
                'source' => $this->displayValue($sourceValue),
                'target' => $this->displayValue($targetValue),
                'source_present' => $sourcePresent,
                'target_state' => $targetState,
            ];

            if ($targetState === 'valid') {
                $valid[] = $field;
            } elseif ($targetState === 'untranslated') {
                $untranslated[] = $field;
                $missing[] = $field;
            } elseif ($targetState === 'missing') {
                $missing[] = $field;
            }
        }

        $expected = array_values(array_unique($expected));
        $missing = array_values(array_unique($missing));
        $sourceMissing = array_values(array_unique($sourceMissing));
        $untranslated = array_values(array_unique($untranslated));
        $hasArabic = collect($fields)->contains(fn (array $field) => $field['target_state'] === 'valid' && $this->containsArabic($field['target']));
        $hasFrenchInArabic = collect($fields)->contains(function (array $field) {
            return $field['source_present']
                && trim($field['target']) !== ''
                && $this->containsSourcePhrase($field['source'], $field['target']);
        });
        $validCount = count(array_intersect($expected, $valid));

        if ($sourceMissing !== []) {
            $status = self::MISSING_FRENCH_SOURCE;
        } elseif ($hasFrenchInArabic) {
            $status = self::CONTAINS_FRENCH_IN_ARABIC;
        } elseif ($expected === [] || count($missing) === count($expected)) {
            $status = self::MISSING_ARABIC;
        } elseif ($validCount === count($expected)) {
            $status = self::COMPLETE;
        } else {
            $status = self::PARTIAL;
        }

        return [
            'status' => $status,
            'source_language' => $this->sourceLanguage(),
            'target_language' => $this->targetLanguage(),
            'fields' => $fields,
            'expected_fields' => $expected,
            'missing_fields' => $missing,
            'source_missing_fields' => $sourceMissing,
            'untranslated_fields' => $untranslated,
            'valid_fields' => $valid,
            'has_arabic_content' => $hasArabic,
            'has_french_in_arabic' => $hasFrenchInArabic,
        ];
    }

    public function sourceValues(Product $product): array
    {
        $diagnosis = $this->diagnose($product);

        return collect($diagnosis['fields'])
            ->filter(fn (array $field) => $field['source_present'] && $field['target_state'] !== 'valid')
            ->mapWithKeys(fn (array $field, string $name) => [$name => $this->rawValue($field['source'])])
            ->all();
    }

    public function containsArabic(?string $value): bool
    {
        $text = $this->humanText($value);
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}]/u', $text) === 1;
    }

    public function containsFrench(?string $value): bool
    {
        $text = $this->humanText($value);
        return preg_match('/[\p{Latin}]/u', $text) === 1;
    }

    private function containsSourcePhrase(?string $source, ?string $target): bool
    {
        $sourceText = $this->humanText($source);
        $targetText = Str::lower($this->humanText($target));

        if (mb_strlen($sourceText) < 4 || !$this->containsFrench($sourceText)) {
            return false;
        }

        return str_contains($targetText, Str::lower($sourceText));
    }

    public function humanText(?string $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('~https?://\S+~i', ' ', $text) ?? $text;
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private function sourceValue(Product $product, $source, string $field): string
    {
        $value = $source?->{$field};
        if ($value !== null && trim(strip_tags((string) $value)) !== '') {
            return (string) $value;
        }

        return $this->sourceLanguage() === (string) config('app.locale', 'fr')
            ? (string) ($product->{$field} ?? '')
            : '';
    }

    private function targetState(string $field, string $sourceValue, string $targetValue): string
    {
        if ($targetValue === '') {
            return 'missing';
        }
        if ($field === 'unit') {
            return $this->sameHumanText($sourceValue, $targetValue) ? 'untranslated' : 'valid';
        }
        if (!$this->containsArabic($targetValue)) {
            return 'untranslated';
        }
        if ($this->sameHumanText($sourceValue, $targetValue) && $this->containsFrench($targetValue)) {
            return 'untranslated';
        }
        return 'valid';
    }

    private function sameHumanText(?string $left, ?string $right): bool
    {
        $normalize = fn (?string $value) => Str::lower(trim(preg_replace('/\s+/u', ' ', $this->humanText($value)) ?? ''));
        return $normalize($left) !== '' && $normalize($left) === $normalize($right);
    }

    private function displayValue(?string $value): string
    {
        return Str::limit((string) $value, 1000, '…');
    }

    private function rawValue(?string $value): string
    {
        return (string) $value;
    }
}

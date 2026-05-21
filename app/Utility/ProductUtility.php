<?php

namespace App\Utility;

use App\Models\Addon;
use App\Models\Attribute;
use App\Models\Color;

class ProductUtility
{
    public static function get_attribute_options($collection)
    {
        if (is_array($collection)) {
            $collection = collect($collection);
        }

        $options = array();
        if (
            $collection->get('colors_active') &&
            $collection->has('colors') &&
            is_array($collection->get('colors')) &&
            count($collection->get('colors')) > 0
        ) {
            $colors_active = 1;
            array_push($options, $collection->get('colors'));
        }

        $removed_sku_variants = collect($collection->get('removed_sku_variants', []))
            ->map(fn ($variant) => strtolower((string) preg_replace('/\s+/', '', trim((string) $variant))))
            ->filter()
            ->values()
            ->all();

        if (isset($collection['choice_no']) && $collection['choice_no']) {
            foreach ($collection['choice_no'] as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                $options_values = isset($collection[$name]) ? $collection[$name] : (request()[$name] ?? []);
                if (is_array($options_values) || is_object($options_values)) {
                    foreach ($options_values as $key => $eachValue) {
                        $normalized = strtolower((string) preg_replace('/\s+/', '', trim((string) $eachValue)));
                        if (!in_array($normalized, $removed_sku_variants, true)) {
                            array_push($data, $eachValue);
                        }
                    }
                }
                array_push($options, $data);
            }
        }

        return $options;
    }

    public static function get_combination_string($combination, $collection)
    {
        $str = '';
        foreach ($combination as $key => $item) {
            if ($key > 0) {
                $str .= '-' . str_replace(' ', '', $item);
            } else {
                if ($collection->get('colors_active') && $collection->has('colors') && is_array($collection->get('colors')) && count($collection->get('colors')) > 0) {
                    $color_name = Color::where('code', $item)->first()->name;
                    $str .= $color_name;
                } else {
                    $str .= str_replace(' ', '', $item);
                }
            }
        }
        return $str;
    }

    public static function includeSavedDimensionOccurrences(array $values, $product, $attributeId): array
    {
        $attribute = Attribute::find($attributeId);
        if ((int) $attributeId !== 35 && strtolower((string) optional($attribute)->name) !== 'dimension') {
            return $values;
        }

        $submittedCounts = [];
        foreach ($values as $value) {
            $normalized = self::normalizeVariantValue($value);
            $submittedCounts[$normalized] = ($submittedCounts[$normalized] ?? 0) + 1;
        }

        $savedValues = [];
        foreach ($product->stocks as $stock) {
            $normalized = self::normalizeVariantValue($stock->variant);
            if (!isset($submittedCounts[$normalized])) {
                continue;
            }

            $savedValues[$normalized][] = $stock->variant;
        }

        foreach ($savedValues as $normalized => $stockValues) {
            $missingOccurrences = count($stockValues) - ($submittedCounts[$normalized] ?? 0);
            if ($missingOccurrences <= 0) {
                continue;
            }

            $values = array_merge($values, array_slice($stockValues, -$missingOccurrences));
        }

        return $values;
    }

    private static function normalizeVariantValue($value): string
    {
        return strtolower((string) preg_replace('/\s+/', '', trim((string) $value)));
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Str;

class SearchQueryNormalizer
{
    /**
     * Normalize a query for matching and privacy-safe telemetry without
     * changing the original value that should be shown back to the customer.
     *
     * @return array{
     *     original:string,
     *     normalized:string,
     *     tokens:array<int,string>,
     *     hash:string,
     *     locale:string,
     *     is_empty:bool,
     *     is_truncated:bool,
     *     language_signals:array<string,bool>
     * }
     */
    public function normalize(?string $query, ?string $locale = null): array
    {
        $original = trim((string) $query);
        $maxLength = max(1, (int) config('search.query.max_length', 120));
        $isTruncated = mb_strlen($original, 'UTF-8') > $maxLength;
        $bounded = mb_substr($original, 0, $maxLength, 'UTF-8');

        // Remove control characters only. Digits remain available for prices,
        // dimensions, SKUs, and Arabizi token detection in later phases.
        $bounded = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $bounded) ?? '';
        $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $bounded) ?? ''), 'UTF-8');

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_unique(array_map(
            static fn (string $token): string => trim($token),
            $tokens
        )));

        $resolvedLocale = $locale ?: app()->getLocale();
        $resolvedLocale = str_replace('_', '-', (string) ($resolvedLocale ?: config('search.locales.default', 'fr')));

        return [
            'original' => $original,
            'normalized' => $normalized,
            'tokens' => $tokens,
            'hash' => hash('sha256', $normalized),
            'locale' => $resolvedLocale,
            'is_empty' => $normalized === '',
            'is_truncated' => $isTruncated,
            'language_signals' => [
                'has_arabic' => preg_match('/\p{Arabic}/u', $normalized) === 1,
                'has_latin' => preg_match('/[A-Za-z]/', $normalized) === 1,
                'has_digits' => preg_match('/\d/', $normalized) === 1,
                'mixed_script' => preg_match('/\p{Arabic}/u', $normalized) === 1
                    && preg_match('/[A-Za-z]/', $normalized) === 1,
            ],
        ];
    }

    public function isWithinBounds(?string $query): bool
    {
        $result = $this->normalize($query);
        $length = mb_strlen($result['normalized'], 'UTF-8');
        $minimum = max(0, (int) config('search.query.min_length', 2));

        return !$result['is_empty'] && $length >= $minimum && !$result['is_truncated'];
    }
}

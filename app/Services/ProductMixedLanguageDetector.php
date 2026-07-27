<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductMixedLanguageDetector
{
    /**
     * Words that are distinctive enough to be useful as English product-content
     * signals. This is intentionally conservative: the export is for review,
     * not for automatic translation or data mutation.
     *
     * @var array<int, string>
     */
    private const ENGLISH_TERMS = [
        'and', 'baby', 'bathroom', 'bed', 'black', 'box', 'boxes', 'chair',
        'cover', 'custom', 'drawer', 'drawers', 'door', 'doors', 'forest',
        'glass', 'home', 'indoor', 'kitchen', 'leaf', 'living', 'modern',
        'natural', 'new', 'organizer', 'outdoor', 'pack', 'room', 'rope',
        'round', 'small', 'square', 'storage', 'the', 'wall', 'white', 'wood',
        'with',
    ];

    /**
     * @var array<int, string>
     */
    private const FRENCH_TERMS = [
        'avec', 'bebe', 'bébé', 'boite', 'boîtes', 'boîte', 'boites', 'carre', 'carré', 'carree', 'carrée', 'couvercle',
        'de', 'des', 'dans', 'du', 'et', 'feuille', 'feuilles', 'motif',
        'moderne', 'organisateur', 'paniers', 'personnalise', 'pour',
        'rangement', 'sur', 'tissu', 'toile', 'une', 'un', 'naturelles',
        'corde', 'compartiment', 'compartiments', 'plateau', 'portes',
    ];

    public function __construct(private readonly ProductTranslationStatusService $statusService)
    {
    }

    /**
     * Analyze the configured source-language content of one product.
     *
     * @return array{product_id:int, product_name:string, fields:array<int,string>, french_terms:array<int,string>, english_terms:array<int,string>, preview:string}|null
     */
    public function analyze(Product $product, ?string $language = null): ?array
    {
        $language ??= $this->statusService->sourceLanguage();
        $translation = $product->relationLoaded('product_translations')
            ? $product->product_translations->firstWhere('lang', $language)
            : $product->product_translations()->where('lang', $language)->first();

        $matchedFields = [];
        $frenchTerms = [];
        $englishTerms = [];
        $previews = [];

        foreach ($this->statusService->fields() as $field) {
            $value = $translation?->{$field};
            if (($value === null || trim((string) $value) === '') && $language === (string) config('app.locale', 'fr')) {
                $value = $product->{$field} ?? null;
            }

            $analysis = $this->analyzeText((string) $value);
            if (!$analysis['mixed']) {
                continue;
            }

            $matchedFields[] = $field;
            $frenchTerms = array_merge($frenchTerms, $analysis['french_terms']);
            $englishTerms = array_merge($englishTerms, $analysis['english_terms']);
            $previews[] = $field.': '.$analysis['preview'];
        }

        if ($matchedFields === []) {
            return null;
        }

        return [
            'product_id' => (int) $product->id,
            'product_name' => (string) $product->name,
            'fields' => array_values(array_unique($matchedFields)),
            'french_terms' => $this->uniqueTerms($frenchTerms),
            'english_terms' => $this->uniqueTerms($englishTerms),
            'preview' => Str::limit(implode(' | ', $previews), 1000, '...'),
        ];
    }

    /**
     * @return array{mixed:bool, french_terms:array<int,string>, english_terms:array<int,string>, preview:string}
     */
    public function analyzeText(?string $value): array
    {
        $text = $this->humanText($value);
        if ($text === '') {
            return ['mixed' => false, 'french_terms' => [], 'english_terms' => [], 'preview' => ''];
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', Str::lower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $frenchTerms = array_values(array_unique(array_intersect($tokens, self::FRENCH_TERMS)));
        $englishTerms = array_values(array_unique(array_intersect($tokens, self::ENGLISH_TERMS)));
        $hasFrenchAccent = preg_match('/[àâäçéèêëîïôöùûüÿœæ]/u', $text) === 1;
        $hasFrenchSignal = $hasFrenchAccent || count($frenchTerms) >= 2;

        return [
            'mixed' => $hasFrenchSignal && $englishTerms !== [],
            'french_terms' => $frenchTerms,
            'english_terms' => $englishTerms,
            'preview' => Str::limit($text, 500, '...'),
        ];
    }

    private function humanText(?string $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('~https?://\S+~i', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /**
     * @param array<int, string> $terms
     * @return array<int, string>
     */
    private function uniqueTerms(array $terms): array
    {
        return array_values(array_unique($terms));
    }
}

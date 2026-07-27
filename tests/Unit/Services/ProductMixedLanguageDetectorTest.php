<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductTranslation;
use App\Services\ProductMixedLanguageDetector;
use App\Services\ProductTranslationStatusService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProductMixedLanguageDetectorTest extends TestCase
{
    public function test_it_detects_french_and_english_in_the_same_field(): void
    {
        $product = $this->productWithSource([
            'name' => 'Boîte de rangement Black Box',
            'description' => 'Rangement avec couvercle pour la maison',
        ]);

        $match = app(ProductMixedLanguageDetector::class)->analyze($product);

        $this->assertNotNull($match);
        $this->assertContains('name', $match['fields']);
        $this->assertContains('black', $match['english_terms']);
        $this->assertContains('boîte', $match['french_terms']);
    }

    public function test_it_ignores_pure_french_and_pure_english_fields(): void
    {
        $product = $this->productWithSource([
            'name' => 'Boîte de rangement avec couvercle',
            'description' => 'Black storage box with cover',
        ]);

        $this->assertNull(app(ProductMixedLanguageDetector::class)->analyze($product));
    }

    /**
     * @param array<string, string> $fields
     */
    private function productWithSource(array $fields): Product
    {
        $product = new Product();
        $product->forceFill(['id' => 1, 'name' => $fields['name'] ?? 'Produit']);
        $translation = new ProductTranslation($fields + ['lang' => 'fr', 'product_id' => 1]);
        $product->setRelation('product_translations', new Collection([$translation]));

        return $product;
    }
}

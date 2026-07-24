<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductTranslation;
use App\Services\ProductTranslationStatusService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class ProductTranslationStatusServiceTest extends TestCase
{
    private ProductTranslationStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.locale' => 'fr',
            'product_translation.source_language' => 'fr',
            'product_translation.target_language' => 'ma',
            'product_translation.fields' => ['name', 'unit', 'description'],
            'product_translation.required_fields' => ['name'],
        ]);
        $this->service = app(ProductTranslationStatusService::class);
    }

    public function test_it_classifies_complete_translation(): void
    {
        $product = $this->product('Bureau mural', 'pièce', '<p>Description française</p>');
        $product->setRelation('product_translations', new Collection([
            ProductTranslation::make(['lang' => 'ma', 'name' => 'مكتب جداري', 'unit' => 'قطعة', 'description' => '<p>وصف عربي</p>']),
        ]));

        $diagnosis = $this->service->diagnose($product);

        $this->assertSame(ProductTranslationStatusService::COMPLETE, $diagnosis['status']);
        $this->assertSame(['name', 'unit', 'description'], $diagnosis['valid_fields']);
    }

    public function test_it_includes_localized_seo_fields_in_translation_status(): void
    {
        config([
            'product_translation.fields' => ['name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
            'product_translation.required_fields' => ['name'],
        ]);

        $product = new Product([
            'meta_title' => 'Titre SEO',
            'meta_description' => 'Description SEO',
            'meta_keywords' => 'rangement, maison',
        ]);
        $product->setRelation('product_translations', new Collection([
            ProductTranslation::make([
                'lang' => 'fr',
                'name' => 'Bureau',
                'unit' => 'pièce',
                'description' => 'Description',
                'meta_title' => 'Titre SEO',
                'meta_description' => 'Description SEO',
                'meta_keywords' => 'rangement, maison',
            ]),
            ProductTranslation::make([
                'lang' => 'ma',
                'name' => 'مكتب',
                'unit' => 'قطعة',
                'description' => 'وصف',
                'meta_title' => 'عنوان تحسين محركات البحث',
                'meta_description' => 'وصف تحسين محركات البحث',
                'meta_keywords' => 'ترتيب, منزل',
            ]),
        ]));

        $diagnosis = $this->service->diagnose($product);

        $this->assertSame(ProductTranslationStatusService::COMPLETE, $diagnosis['status']);
        $this->assertSame(
            ['name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
            $diagnosis['valid_fields']
        );
    }

    public function test_it_detects_missing_arabic_and_copied_french(): void
    {
        $product = $this->product('Bureau mural', 'pièce', 'Description française');
        $product->setRelation('product_translations', new Collection([
            ProductTranslation::make(['lang' => 'ma', 'name' => 'Bureau mural', 'unit' => 'pièce', 'description' => '']),
        ]));

        $diagnosis = $this->service->diagnose($product);

        $this->assertSame(ProductTranslationStatusService::CONTAINS_FRENCH_IN_ARABIC, $diagnosis['status']);
        $this->assertSame(['name', 'unit', 'description'], $diagnosis['missing_fields']);
        $this->assertContains('name', $diagnosis['untranslated_fields']);
    }

    public function test_it_uses_base_product_columns_as_french_source_and_flags_missing_source(): void
    {
        $product = $this->product('', '', '');
        $product->setRelation('product_translations', new Collection([
            ProductTranslation::make(['lang' => 'ma', 'name' => 'مكتب', 'unit' => 'قطعة', 'description' => 'وصف']),
        ]));

        $diagnosis = $this->service->diagnose($product);

        $this->assertSame(ProductTranslationStatusService::MISSING_FRENCH_SOURCE, $diagnosis['status']);
        $this->assertContains('name', $diagnosis['source_missing_fields']);
    }

    public function test_mixed_arabic_and_latin_brand_content_is_valid(): void
    {
        $product = $this->product('Chaise', 'unité', 'Chaise moderne');
        $product->setRelation('product_translations', new Collection([
            ProductTranslation::make(['lang' => 'ma', 'name' => 'كرسي MAYUSH Pro', 'unit' => 'وحدة', 'description' => 'كرسي عربي']),
        ]));

        $this->assertSame(ProductTranslationStatusService::COMPLETE, $this->service->diagnose($product)['status']);
    }

    private function product(string $name, string $unit, string $description): Product
    {
        return Product::make(compact('name', 'unit', 'description'));
    }
}

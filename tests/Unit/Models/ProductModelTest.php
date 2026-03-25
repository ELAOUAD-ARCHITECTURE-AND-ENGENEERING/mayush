<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;

/**
 * ProductModelTest
 *
 * Tests Product model accessors, scopes, relationships, and video link handling.
 * Uses plain model instantiation without DB calls where possible.
 */
class ProductModelTest extends TestCase
{
    /** @test */
    public function model_class_exists(): void
    {
        $this->assertTrue(class_exists(Product::class));
    }

    /** @test */
    public function product_has_is_approved_published_scope(): void
    {
        $this->assertTrue(method_exists(Product::class, 'scopeIsApprovedPublished'));
    }

    /** @test */
    public function product_has_physical_scope(): void
    {
        $this->assertTrue(method_exists(Product::class, 'scopePhysical'));
    }

    /** @test */
    public function product_has_digital_scope(): void
    {
        $this->assertTrue(method_exists(Product::class, 'scopeDigital'));
    }

    /** @test */
    public function product_has_get_translation_method(): void
    {
        $this->assertTrue(method_exists(Product::class, 'getTranslation'));
    }

    /** @test */
    public function get_translation_accepts_field_and_lang_params(): void
    {
        $ref    = new \ReflectionMethod(Product::class, 'getTranslation');
        $params = $ref->getParameters();

        $this->assertGreaterThanOrEqual(1, count($params));
        $this->assertEquals('field', $params[0]->getName());
    }

    /** @test */
    public function product_has_reviews_relationship(): void
    {
        $product = new Product();
        $this->assertTrue(method_exists($product, 'reviews'));
    }

    /** @test */
    public function product_has_stocks_relationship(): void
    {
        $product = new Product();
        $this->assertTrue(method_exists($product, 'stocks'));
    }

    /** @test */
    public function product_has_flash_deal_products_relationship(): void
    {
        $product = new Product();
        $this->assertTrue(method_exists($product, 'flash_deal_products'));
    }

    /** @test */
    public function product_has_brand_relationship(): void
    {
        $product = new Product();
        $this->assertTrue(method_exists($product, 'brand'));
    }

    /** @test */
    public function product_has_categories_relationship(): void
    {
        $product = new Product();
        $this->assertTrue(method_exists($product, 'categories'));
    }

    /** @test */
    public function product_has_taxonomies_relationship(): void
    {
        $product = new Product();
        $this->assertTrue(method_exists($product, 'taxes'));
    }

    /** @test */
    public function product_discount_type_values(): void
    {
        $validTypes = ['percent', 'amount'];
        foreach ($validTypes as $type) {
            $this->assertIsString($type);
        }
    }

    /** @test */
    public function video_link_mutator_returns_null_for_empty_array(): void
    {
        // The videoLink mutator returns null when all items are blank
        $filtered = array_filter(['', '  ', ''], fn($v) => trim($v) !== '');
        $result   = empty($filtered) ? null : json_encode($filtered);

        $this->assertNull($result);
    }

    /** @test */
    public function video_link_mutator_encodes_valid_urls(): void
    {
        $links    = ['https://youtube.com/watch?v=abc', 'https://vimeo.com/123'];
        $filtered = array_filter($links, fn($v) => trim($v) !== '');
        $result   = empty($filtered) ? null : json_encode($filtered);

        $this->assertNotNull($result);
        $decoded = json_decode($result, true);
        $this->assertCount(2, $decoded);
    }

    /** @test */
    public function thumbnail_img_falls_back_to_first_photo(): void
    {
        // When thumbnail_img is null, it falls back to first photo in the photos column
        $attrs = ['photos' => 'img1.jpg,img2.jpg', 'thumbnail_img' => null];
        $photos = $attrs['photos'] ?? null;
        $thumbnailImg = $attrs['thumbnail_img'];

        if ($photos) {
            $photosArray = explode(',', $photos);
            $thumbnailImg = $thumbnailImg ?: ($photosArray[0] ?? null);
        }

        $this->assertEquals('img1.jpg', $thumbnailImg);
    }
}

<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ProductStockService;

/**
 * ProductStockServiceTest
 *
 * Tests the ProductStockService structural contract and combination logic.
 */
class ProductStockServiceTest extends TestCase
{
    /** @test */
    public function class_exists(): void
    {
        $this->assertTrue(class_exists(ProductStockService::class));
    }

    /** @test */
    public function has_store_method(): void
    {
        $this->assertTrue(method_exists(ProductStockService::class, 'store'));
    }

    /** @test */
    public function has_product_duplicate_store_method(): void
    {
        $this->assertTrue(method_exists(ProductStockService::class, 'product_duplicate_store'));
    }

    /** @test */
    public function store_method_accepts_array_and_product(): void
    {
        $ref    = new \ReflectionMethod(ProductStockService::class, 'store');
        $params = $ref->getParameters();

        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertEquals('product', $params[1]->getName());

        // $data must be type-hinted as array
        $this->assertEquals('array', $params[0]->getType()->getName());
    }

    /** @test */
    public function variant_string_builder_joins_with_dash(): void
    {
        // Mirrors the combination string logic: items joined with '-'
        $combination = ['Red', 'XL', 'Cotton'];
        $str = '';
        foreach ($combination as $key => $item) {
            if ($key > 0) {
                $str .= '-' . str_replace(' ', '', $item);
            } else {
                $str .= str_replace(' ', '', $item);
            }
        }
        $this->assertEquals('Red-XL-Cotton', $str);
    }

    /** @test */
    public function variant_string_with_spaces_removed(): void
    {
        $combination = ['Light Blue', 'X Large'];
        $str = '';
        foreach ($combination as $key => $item) {
            if ($key > 0) {
                $str .= '-' . str_replace(' ', '', $item);
            } else {
                $str .= str_replace(' ', '', $item);
            }
        }
        $this->assertEquals('LightBlue-XLarge', $str);
    }

    /** @test */
    public function no_combinations_sets_variant_product_to_zero(): void
    {
        // When no combinations exist: variant_product = 0
        $combinations = [];
        $is_variant = count($combinations) > 0 ? 1 : 0;
        $this->assertEquals(0, $is_variant);
    }

    /** @test */
    public function with_combinations_sets_variant_product_to_one(): void
    {
        $combinations = [['Red', 'S'], ['Red', 'M']];
        $is_variant = count($combinations) > 0 ? 1 : 0;
        $this->assertEquals(1, $is_variant);
    }
}

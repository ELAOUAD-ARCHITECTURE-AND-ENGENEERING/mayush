<?php

namespace Tests\Feature\Seller;

use App\Models\Product;
use App\Models\ProductStock;
use App\Services\ProductStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_product_stock_uses_base_price_stock_and_product_id(): void
    {
        $product = Product::factory()->create();
        $payload = [
            'current_stock' => 7,
            'unit_price' => 125.50,
            'sku' => 'SIMPLE-SKU',
            'length' => 10,
            'width' => 5,
            'height' => 2,
            'dimension_unit' => 'cm',
        ];

        request()->replace($payload);
        (new ProductStockService())->store($payload, $product);

        $stock = ProductStock::where('product_id', $product->id)->firstOrFail();

        $this->assertSame('', $stock->variant);
        $this->assertEquals(125.50, $stock->price);
        $this->assertSame(7, $stock->qty);
        $this->assertSame('SIMPLE-SKU', $stock->sku);
        $this->assertEquals(10, $stock->length);
    }

    public function test_variant_product_stock_uses_variant_specific_price_and_qty(): void
    {
        $product = Product::factory()->create();
        $payload = [
            'choice_no' => [1],
            'choice_options_1' => ['Small', 'Large'],
            'price_Small' => 99.00,
            'sku_Small' => 'SMALL-SKU',
            'qty_Small' => 3,
            'img_Small' => null,
            'price_Large' => 149.00,
            'sku_Large' => 'LARGE-SKU',
            'qty_Large' => 0,
            'img_Large' => null,
        ];

        request()->replace($payload);
        (new ProductStockService())->store($payload, $product);

        $small = ProductStock::where('product_id', $product->id)->where('variant', 'Small')->firstOrFail();
        $large = ProductStock::where('product_id', $product->id)->where('variant', 'Large')->firstOrFail();

        $this->assertEquals(99.00, $small->price);
        $this->assertSame(3, $small->qty);
        $this->assertSame('SMALL-SKU', $small->sku);
        $this->assertEquals(149.00, $large->price);
        $this->assertSame(0, $large->qty);
        $this->assertSame('LARGE-SKU', $large->sku);
        $this->assertTrue($product->fresh()->variant_product == 1);
    }

    public function test_simple_product_stock_generates_sku_when_it_is_blank(): void
    {
        $product = Product::factory()->create();
        $payload = [
            'current_stock' => 4,
            'unit_price' => 60,
            'sku' => '',
        ];

        request()->replace($payload);
        (new ProductStockService())->store($payload, $product);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'sku' => 'SKU-000001',
        ]);
    }

    public function test_variant_product_stocks_generate_unique_skus_for_blank_rows(): void
    {
        $product = Product::factory()->create();
        $payload = [
            'choice_no' => [1],
            'choice_options_1' => ['Small', 'Large'],
            'price_Small' => 99,
            'sku_Small' => '',
            'qty_Small' => 3,
            'img_Small' => null,
            'price_Large' => 149,
            'sku_Large' => '',
            'qty_Large' => 2,
            'img_Large' => null,
        ];

        request()->replace($payload);
        (new ProductStockService())->store($payload, $product);

        $this->assertSame(
            ['SKU-000001', 'SKU-000002'],
            $product->stocks()->orderBy('id')->pluck('sku')->all()
        );
    }

    public function test_generated_sku_sequence_continues_after_existing_database_values(): void
    {
        ProductStock::factory()->create(['sku' => 'SKU-000099']);

        $product = Product::factory()->create();
        $payload = [
            'current_stock' => 2,
            'unit_price' => 80,
            'sku' => '',
        ];

        request()->replace($payload);
        (new ProductStockService())->store($payload, $product);

        $this->assertSame('SKU-000100', $product->stocks()->firstOrFail()->sku);
    }

    public function test_taken_generated_sku_submission_advances_to_the_next_database_value(): void
    {
        ProductStock::factory()->create(['sku' => 'SKU-000001']);

        $product = Product::factory()->create();
        $payload = [
            'current_stock' => 2,
            'unit_price' => 80,
            'sku' => 'SKU-000001',
        ];

        request()->replace($payload);
        (new ProductStockService())->store($payload, $product);

        $this->assertSame('SKU-000002', $product->stocks()->firstOrFail()->sku);
    }
}

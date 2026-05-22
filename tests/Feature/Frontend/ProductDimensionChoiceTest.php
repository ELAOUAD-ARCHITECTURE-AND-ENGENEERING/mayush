<?php

namespace Tests\Feature\Frontend;

use App\Models\Attribute;
use App\Models\Cart;
use App\Models\Product;
use App\Utility\CartUtility;
use App\Utility\ProductUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDimensionChoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dimension_only_choices_use_exact_stock_dimensions_for_legacy_range_variants(): void
    {
        $attribute = $this->dimensionAttribute();
        $product = Product::factory()->create([
            'choice_options' => json_encode([[
                'attribute_id' => $attribute->id,
                'values' => ['101-500cm', '101-500cm'],
            ]]),
            'colors' => json_encode([]),
        ]);

        $first = $product->stocks()->create([
            'variant' => '101-500cm',
            'price' => 3500,
            'qty' => 5,
            'length' => 60,
            'width' => 80,
            'height' => 0,
            'dimension_unit' => 'cm',
        ]);
        $second = $product->stocks()->create([
            'variant' => '101-500cm',
            'price' => 3700,
            'qty' => 5,
            'length' => 80,
            'width' => 120,
            'height' => 0,
            'dimension_unit' => 'cm',
        ]);

        $choice = json_decode($product->choice_options)[0];
        $values = ProductUtility::frontendChoiceValues($product->fresh('stocks'), $choice);

        $this->assertSame([
            ['value' => '60x80x0cm', 'label' => '60 x 80 cm'],
            ['value' => '80x120x0cm', 'label' => '80 x 120 cm'],
        ], $values);
        $this->assertTrue(CartUtility::find_product_stock($product->fresh('stocks'), '60x80x0cm')->is($first));
        $this->assertTrue(CartUtility::find_product_stock($product->fresh('stocks'), '80 x 120 x 0 cm')->is($second));
    }

    public function test_variant_price_uses_exact_dimension_selection_for_legacy_range_stocks(): void
    {
        $attribute = $this->dimensionAttribute();
        $product = Product::factory()->create([
            'choice_options' => json_encode([[
                'attribute_id' => $attribute->id,
                'values' => ['101-500cm', '101-500cm'],
            ]]),
            'colors' => json_encode([]),
        ]);

        $product->stocks()->create([
            'variant' => '101-500cm',
            'sku' => 'DIM-SMALL',
            'price' => 3500,
            'qty' => 5,
            'length' => 60,
            'width' => 80,
            'height' => 0,
            'dimension_unit' => 'cm',
        ]);
        $product->stocks()->create([
            'variant' => '101-500cm',
            'sku' => 'DIM-LARGE',
            'price' => 3700,
            'qty' => 5,
            'length' => 80,
            'width' => 120,
            'height' => 0,
            'dimension_unit' => 'cm',
        ]);

        $this->post(route('products.variant_price'), [
            'id' => $product->id,
            'quantity' => 1,
            'attribute_id_' . $attribute->id => '80x120x0cm',
        ])->assertOk()->assertJson([
            'variation' => '80x120x0cm',
            'sku' => 'DIM-LARGE',
            'length' => 80,
            'width' => 120,
            'height' => 0,
            'dimension_unit' => 'cm',
        ]);
    }

    public function test_cart_uses_exact_dimension_selection_for_legacy_range_stocks(): void
    {
        $attribute = $this->dimensionAttribute();
        $product = Product::factory()->create([
            'choice_options' => json_encode([[
                'attribute_id' => $attribute->id,
                'values' => ['101-500cm', '101-500cm'],
            ]]),
            'colors' => json_encode([]),
            'min_qty' => 1,
        ]);

        $product->stocks()->create([
            'variant' => '101-500cm',
            'price' => 3500,
            'qty' => 5,
            'length' => 60,
            'width' => 80,
            'height' => 0,
            'dimension_unit' => 'cm',
        ]);
        $product->stocks()->create([
            'variant' => '101-500cm',
            'price' => 3700,
            'qty' => 5,
            'length' => 80,
            'width' => 120,
            'height' => 0,
            'dimension_unit' => 'cm',
        ]);

        $this->post(route('cart.addToCart'), [
            'id' => $product->id,
            'quantity' => 1,
            'attribute_id_' . $attribute->id => '80x120x0cm',
        ])->assertOk();

        $cart = Cart::where('product_id', $product->id)->firstOrFail();
        $this->assertSame('80x120x0cm', $cart->variation);
        $this->assertEquals(3700, $cart->price);
    }

    private function dimensionAttribute(): Attribute
    {
        $attribute = new Attribute();
        $attribute->name = 'Dimension';
        $attribute->save();

        return $attribute;
    }
}

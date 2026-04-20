<?php

namespace Database\Factories;

use App\Models\ProductStock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductStockFactory extends Factory
{
    protected $model = ProductStock::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'variant'    => '',
            'sku'        => $this->faker->unique()->ean13,
            'price'      => 100,
            'qty'        => 50,
            'image'      => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition()
    {
        return [
            'owner_id' => 1,
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'variation' => null,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'tax' => $this->faker->randomFloat(2, 0, 100),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 50),
            'discount' => 0,
            'quantity' => $this->faker->numberBetween(1, 5),
            'status' => 1,
        ];
    }
}

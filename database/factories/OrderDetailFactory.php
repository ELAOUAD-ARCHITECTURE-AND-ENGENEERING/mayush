<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailFactory extends Factory
{
    protected $model = OrderDetail::class;

    public function definition(): array
    {
        return [
            'order_id'          => Order::factory(),
            'seller_id'         => User::factory()->seller(),
            'product_id'        => Product::factory(),
            'variation'         => '',
            'price'             => $this->faker->randomFloat(2, 10, 200),
            'tax'               => $this->faker->randomFloat(2, 1, 15),
            'shipping_cost'     => $this->faker->randomFloat(2, 5, 50),
            'quantity'          => $this->faker->numberBetween(1, 5),
            'payment_status'    => 'unpaid',
            'delivery_status'   => 'pending',
            'shipping_type'     => 'home_delivery',
            'reviewed'          => 0,
            'refund_days'       => 0,
        ];
    }

    /** Set as digital product (no shipping). */
    public function digital(): static
    {
        return $this->state([
            'shipping_cost' => 0,
            'shipping_type' => 'digital',
        ]);
    }

    /** Set as paid. */
    public function paid(): static
    {
        return $this->state([
            'payment_status' => 'paid',
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'seller_id'         => User::factory()->seller(),
            'code'              => strtoupper(Str::random(8)),
            'shipping_address'  => json_encode([
                'name'    => $this->faker->name(),
                'email'   => $this->faker->email(),
                'phone'   => $this->faker->phoneNumber(),
                'address' => $this->faker->streetAddress(),
                'city'    => $this->faker->city(),
                'country' => 'US',
                'postal_code' => $this->faker->postcode(),
            ]),
            'payment_type'      => 'cash_on_delivery',
            'payment_status'    => 'unpaid',
            'delivery_status'   => 'pending',
            'grand_total'       => $this->faker->randomFloat(2, 20, 500),
            'payment_status_viewed' => '0',
            'delivery_viewed'   => '0',
            'commission_calculated' => 0,
        ];
    }

    /** Paid order. */
    public function paid(): static
    {
        return $this->state([
            'payment_status' => 'paid',
            'payment_type'   => 'cash_on_delivery',
        ]);
    }

    /** Delivered order. */
    public function delivered(): static
    {
        return $this->state([
            'delivery_status' => 'delivered',
            'payment_status'  => 'paid',
        ]);
    }

    /** Cancelled order. */
    public function cancelled(): static
    {
        return $this->state([
            'delivery_status' => 'cancelled',
        ]);
    }
}

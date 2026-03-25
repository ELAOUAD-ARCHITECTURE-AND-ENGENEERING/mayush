<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'user_id'     => null,  // null = global coupon
            'type'        => 'cart_base',
            'code'        => strtoupper($this->faker->unique()->lexify('COUP????')),
            'start_date'  => now()->subDay()->toDateString(),
            'end_date'    => now()->addDays(30)->toDateString(),
            'discount'    => 10,
            'discount_type' => 'percent',
            'min_buy'     => 50,
            'max_discount' => 100,
        ];
    }

    /** Expired coupon. */
    public function expired(): static
    {
        return $this->state([
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date'   => now()->subDay()->toDateString(),
        ]);
    }

    /** Fixed-amount coupon. */
    public function fixedAmount(float $amount = 20): static
    {
        return $this->state([
            'discount'      => $amount,
            'discount_type' => 'amount',
        ]);
    }

    /** Free-shipping coupon. */
    public function freeShipping(): static
    {
        return $this->state(['type' => 'free_shipping']);
    }
}

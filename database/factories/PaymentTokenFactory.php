<?php

namespace Database\Factories;

use App\Models\PaymentToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTokenFactory extends Factory
{
    protected $model = PaymentToken::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'gateway' => 'cmi',
            'token' => 'test_token_' . $this->faker->unique()->uuid(),
            'card_last_four' => $this->faker->numerify('####'),
            'card_brand' => $this->faker->randomElement(['visa', 'mastercard', 'amex']),
            'card_expiry_month' => $this->faker->numberBetween(1, 12),
            'card_expiry_year' => $this->faker->numberBetween(now()->year + 1, now()->year + 5),
            'is_active' => true,
            'is_default' => false,
            'card_fingerprint' => $this->faker->sha256(),
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }

    public function expired(): static
    {
        return $this->state([
            'card_expiry_month' => now()->month,
            'card_expiry_year' => now()->year - 1,
        ]);
    }

    public function cmi(): static
    {
        return $this->state(['gateway' => 'cmi']);
    }

    public function stripe(): static
    {
        return $this->state(['gateway' => 'stripe']);
    }
}
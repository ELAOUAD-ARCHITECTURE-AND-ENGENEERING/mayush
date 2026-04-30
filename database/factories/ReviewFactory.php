<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id'    => User::factory(),
            'rating'     => $this->faker->numberBetween(1, 5),
            'comment'    => $this->faker->sentence(),
            'photos'     => '',
            'viewed'     => 0,
            'status'     => 0,
            'type'       => 'real',
        ];
    }

    /** A published (status=1) review. */
    public function published(): static
    {
        return $this->state(['status' => 1]);
    }

    /** A custom (admin-submitted) review. */
    public function custom(): static
    {
        return $this->state([
            'type' => 'custom',
            'user_id' => null,
            'custom_reviewer_name' => $this->faker->name(),
        ]);
    }
}

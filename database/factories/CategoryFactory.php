<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name'          => $name,
            'slug'          => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 9999),
            'banner'        => null,
            'icon'          => null,
            'featured'      => 0,
            'top'           => 0,
            'level'         => 0,
            'order_level'   => 0,
            'commision_rate' => 0,
            'meta_title'    => $name,
            'meta_description' => $this->faker->sentence(),
            'digital'       => 0,
        ];
    }

    /** Featured category. */
    public function featured(): static
    {
        return $this->state(['featured' => 1]);
    }
}

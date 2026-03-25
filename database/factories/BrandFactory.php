<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        return [
            'name'          => $name,
            'slug'          => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 9999),
            'logo'          => null,
            'top'           => 0,
            'meta_title'    => $name,
            'meta_description' => $this->faker->sentence(),
        ];
    }

    /** Top brand. */
    public function top(): static
    {
        return $this->state(['top' => 1]);
    }
}

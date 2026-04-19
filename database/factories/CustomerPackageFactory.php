<?php

namespace Database\Factories;

use App\Models\CustomerPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPackageFactory extends Factory
{
    protected $model = CustomerPackage::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'amount' => 0,
            'min_spend' => 0,
            'loyalty_multiplier' => 1.0,
            'tier_level' => 0,
            'is_loyalty_tier' => true,
            'product_upload' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

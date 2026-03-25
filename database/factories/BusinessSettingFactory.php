<?php

namespace Database\Factories;

use App\Models\BusinessSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessSettingFactory extends Factory
{
    protected $model = BusinessSetting::class;

    public function definition()
    {
        return [
            'type' => $this->faker->word,
            'value' => $this->faker->sentence,
        ];
    }
}

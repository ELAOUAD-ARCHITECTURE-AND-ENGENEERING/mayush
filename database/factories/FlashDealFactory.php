<?php

namespace Database\Factories;

use App\Models\FlashDeal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FlashDealFactory extends Factory
{
    protected $model = FlashDeal::class;

    public function definition(): array
    {
        $now = now();
        return [
            'title'      => $this->faker->words(3, true),
            'slug'       => Str::slug($this->faker->words(3, true)) . '-' . $this->faker->unique()->numberBetween(100, 9999),
            'start_date' => $now->copy()->subHour()->timestamp,
            'end_date'   => $now->copy()->addDays(7)->timestamp,
            'status'     => 1,
            'featured'   => 1,
            'background_color' => '#ff5722',
            'text_color'       => '#ffffff',
        ];
    }

    /** Active deal (currently running). */
    public function active(): static
    {
        $now = now();
        return $this->state([
            'start_date' => $now->copy()->subHour()->timestamp,
            'end_date'   => $now->copy()->addDays(7)->timestamp,
            'status'     => 1,
            'featured'   => 1,
        ]);
    }

    /** Expired deal (end_date in the past). */
    public function expired(): static
    {
        $now = now();
        return $this->state([
            'start_date' => $now->copy()->subDays(10)->timestamp,
            'end_date'   => $now->copy()->subDays(3)->timestamp,
            'status'     => 1,
        ]);
    }

    /** Upcoming deal (start_date in the future). */
    public function upcoming(): static
    {
        $now = now();
        return $this->state([
            'start_date' => $now->copy()->addDays(2)->timestamp,
            'end_date'   => $now->copy()->addDays(9)->timestamp,
            'status'     => 1,
        ]);
    }

    /** Inactive (status = 0). */
    public function inactive(): static
    {
        return $this->state(['status' => 0]);
    }
}

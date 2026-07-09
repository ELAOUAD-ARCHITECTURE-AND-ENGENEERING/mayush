<?php

namespace Database\Factories;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        $extension = $this->faker->randomElement(['jpg', 'png', 'webp', 'gif', 'pdf']);
        return [
            'file_original_name' => $this->faker->word() . '.' . $extension,
            'file_name' => $this->faker->uuid() . '.' . $extension,
            'user_id' => User::factory(),
            'extension' => $extension,
            'type' => in_array($extension, ['jpg', 'png', 'webp', 'gif']) ? 'image' : 'document',
            'file_size' => $this->faker->numberBetween(1000, 5000000),
        ];
    }

    public function image(): static
    {
        return $this->state([
            'extension' => $this->faker->randomElement(['jpg', 'png', 'webp']),
            'type' => 'image',
        ]);
    }

    public function document(): static
    {
        return $this->state([
            'extension' => 'pdf',
            'type' => 'document',
        ]);
    }
}
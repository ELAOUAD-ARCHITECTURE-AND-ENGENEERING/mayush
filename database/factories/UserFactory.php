<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'remember_token'    => Str::random(10),
            'user_type'         => 'customer',
            'phone'             => $this->faker->phoneNumber(),
            'address'           => $this->faker->address(),
            'banned'            => 0,
            'verification_status' => 1,
        ];
    }

    /** Create an admin user. */
    public function admin(): static
    {
        return $this->state(['user_type' => 'admin']);
    }

    /** Create a seller user. */
    public function seller(): static
    {
        return $this->state(['user_type' => 'seller']);
    }

    /** Create a customer user (default). */
    public function customer(): static
    {
        return $this->state(['user_type' => 'customer']);
    }

    /** Create an unverified user. */
    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}

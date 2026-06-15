<?php

namespace Database\Factories;

use App\Models\CombinedOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CombinedOrderFactory extends Factory
{
    protected $model = CombinedOrder::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shipping_address' => json_encode([
                'name'    => $this->faker->name(),
                'email'   => $this->faker->email(),
                'phone'   => $this->faker->phoneNumber(),
                'address' => $this->faker->streetAddress(),
                'city'    => $this->faker->city(),
                'country' => 'Morocco',
                'postal_code' => $this->faker->postcode(),
            ]),
            'grand_total' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'address'     => $this->faker->address(),
            'country_id'  => 1, // Default to Morocco (if seeders match)
            'state_id'    => null,
            'city_id'     => 1,
            'postal_code' => $this->faker->postcode(),
            'area_id'     => null,
            'phone'       => $this->faker->phoneNumber(),
            'set_default' => 0,
            'set_billing' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderTrackingHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderTrackingHistoryFactory extends Factory
{
    protected $model = OrderTrackingHistory::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'status' => $this->faker->randomElement(['processing', 'shipped', 'in_transit', 'out_for_delivery', 'delivered']),
            'location_name' => $this->faker->city,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'notes' => $this->faker->sentence,
            'expected_delivery_date' => $this->faker->dateTimeBetween('now', '+1 week'),
        ];
    }
}

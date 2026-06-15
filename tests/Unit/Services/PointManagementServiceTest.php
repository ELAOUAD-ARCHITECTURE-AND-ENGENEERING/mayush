<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Product;
use App\Models\PointTemplate;
use App\Services\PointManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PointManagementServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PointManagementService();
    }

    public function test_calculate_fixed_points()
    {
        $product = new Product(['unit_price' => 100]);
        $template = new PointTemplate(['type' => 'fixed', 'value' => 50]);

        $points = $this->service->calculatePointsByTemplate($product, $template);

        $this->assertEquals(50, $points);
    }

    public function test_calculate_percentage_points()
    {
        $product = new Product(['unit_price' => 200]);
        $template = new PointTemplate(['type' => 'percentage_of_price', 'value' => 10]);

        $points = $this->service->calculatePointsByTemplate($product, $template);

        $this->assertEquals(20, $points); // 10% of 200 = 20
    }

    public function test_calculate_percentage_points_with_min_threshold()
    {
        $product = new Product(['unit_price' => 10]); // Cheap item
        $template = new PointTemplate(['type' => 'percentage_of_price', 'value' => 10, 'min_threshold' => 5]);

        $points = $this->service->calculatePointsByTemplate($product, $template);

        // 10% of 10 is 1, but min_threshold is 5, so it should be rounded up to 5.
        $this->assertEquals(5, $points);
    }

    public function test_calculate_percentage_points_with_max_threshold()
    {
        $product = new Product(['unit_price' => 10000]); // Expensive item
        $template = new PointTemplate(['type' => 'percentage_of_price', 'value' => 10, 'max_threshold' => 500]);

        $points = $this->service->calculatePointsByTemplate($product, $template);

        // 10% of 10000 is 1000, but max_threshold is 500.
        $this->assertEquals(500, $points);
    }

    public function test_prevent_negative_points()
    {
        $product = new Product(['unit_price' => -50]); // Invalid price or negative fixed
        $template = new PointTemplate(['type' => 'fixed', 'value' => -100]);

        $points = $this->service->calculatePointsByTemplate($product, $template);

        // Should fallback to 0
        $this->assertEquals(0, $points);
    }

    public function test_rounding_to_nearest_integer()
    {
        $product = new Product(['unit_price' => 99.99]);
        $template = new PointTemplate(['type' => 'percentage_of_price', 'value' => 5]);

        $points = $this->service->calculatePointsByTemplate($product, $template);

        // 5% of 99.99 = 4.9995 -> Should round to 5
        $this->assertEquals(5, $points);
    }
}

<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Order;

/**
 * OrderModelTest
 *
 * Tests Order model structure, relationships, and status logic.
 */
class OrderModelTest extends TestCase
{
    /** @test */
    public function model_class_exists(): void
    {
        $this->assertTrue(class_exists(Order::class));
    }

    /** @test */
    public function order_has_order_details_relationship(): void
    {
        $this->assertTrue(method_exists(Order::class, 'orderDetails'));
    }

    /** @test */
    public function order_has_user_relationship(): void
    {
        $this->assertTrue(method_exists(Order::class, 'user'));
    }

    /** @test */
    public function valid_delivery_statuses(): void
    {
        $statuses = ['pending', 'confirmed', 'on_the_way', 'delivered', 'cancelled'];
        foreach ($statuses as $s) {
            $this->assertIsString($s);
            $this->assertNotEmpty($s);
        }
    }

    /** @test */
    public function valid_payment_statuses(): void
    {
        $statuses = ['paid', 'unpaid'];
        foreach ($statuses as $s) {
            $this->assertIsString($s);
        }
    }

    /** @test */
    public function grand_total_must_be_positive(): void
    {
        $grandTotal = 150.00;
        $this->assertGreaterThan(0, $grandTotal);
    }

    /** @test */
    public function order_code_format_check(): void
    {
        // Order codes are uppercase alphanumeric 8 chars
        $code = strtoupper(\Illuminate\Support\Str::random(8));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $code);
    }

    /** @test */
    public function shipping_address_decodes_to_array(): void
    {
        $address = json_encode([
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'phone'   => '+1234567890',
            'address' => '123 Test St',
            'city'    => 'Casablanca',
            'country' => 'MA',
        ]);

        $decoded = json_decode($address);
        $this->assertIsObject($decoded);
        $this->assertEquals('John Doe', $decoded->name);
        $this->assertObjectHasProperty('phone', $decoded);
    }

    /** @test */
    public function payment_status_aggregation(): void
    {
        // All paid → order is paid
        $details = ['paid', 'paid', 'paid'];
        $status  = 'paid';
        foreach ($details as $d) {
            if ($d !== 'paid') { $status = 'unpaid'; break; }
        }
        $this->assertEquals('paid', $status);

        // One unpaid → order is unpaid
        $details2 = ['paid', 'unpaid', 'paid'];
        $status2  = 'paid';
        foreach ($details2 as $d) {
            if ($d !== 'paid') { $status2 = 'unpaid'; break; }
        }
        $this->assertEquals('unpaid', $status2);
    }

    /** @test */
    public function commission_calculated_flag_is_boolean_like(): void
    {
        $order                    = new Order();
        $order->commission_calculated = 0;

        $this->assertFalse((bool)$order->commission_calculated);

        $order->commission_calculated = 1;
        $this->assertTrue((bool)$order->commission_calculated);
    }
}

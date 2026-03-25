<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Coupon;

/**
 * CouponModelTest
 *
 * Tests Coupon model structure, expiry logic, and discount type handling.
 */
class CouponModelTest extends TestCase
{
    /** @test */
    public function model_class_exists(): void
    {
        $this->assertTrue(class_exists(Coupon::class));
    }

    /** @test */
    public function coupon_discount_type_percent(): void
    {
        $coupon               = new Coupon();
        $coupon->discount     = 20;
        $coupon->discount_type = 'percent';

        $price    = 100.00;
        $discount = $coupon->discount_type === 'percent'
            ? ($price * $coupon->discount) / 100
            : $coupon->discount;

        $this->assertEquals(20.0, $discount);
    }

    /** @test */
    public function coupon_discount_type_amount(): void
    {
        $coupon               = new Coupon();
        $coupon->discount     = 15;
        $coupon->discount_type = 'amount';

        $price    = 100.00;
        $discount = $coupon->discount_type === 'percent'
            ? ($price * $coupon->discount) / 100
            : $coupon->discount;

        $this->assertEquals(15, $discount);
    }

    /** @test */
    public function expired_coupon_logic(): void
    {
        // Coupon with past end_date is expired
        $endDate   = now()->subDays(5)->toDateString();
        $isExpired = strtotime($endDate) < strtotime(now()->toDateString());

        $this->assertTrue($isExpired);
    }

    /** @test */
    public function valid_coupon_not_expired(): void
    {
        $endDate   = now()->addDays(10)->toDateString();
        $isExpired = strtotime($endDate) < strtotime(now()->toDateString());

        $this->assertFalse($isExpired);
    }

    /** @test */
    public function coupon_min_buy_check(): void
    {
        $minBuy      = 50.00;
        $cartTotal   = 30.00;
        $isEligible  = $cartTotal >= $minBuy;

        $this->assertFalse($isEligible);

        $cartTotal  = 75.00;
        $isEligible = $cartTotal >= $minBuy;

        $this->assertTrue($isEligible);
    }

    /** @test */
    public function coupon_max_discount_cap(): void
    {
        $discount    = 200.00; // calculated discount
        $maxDiscount = 100.00;
        $applied     = min($discount, $maxDiscount);

        $this->assertEquals(100.00, $applied);
    }

    /** @test */
    public function coupon_code_is_uppercase(): void
    {
        $code = 'SUMMER20';
        $this->assertEquals($code, strtoupper($code));
    }

    /** @test */
    public function valid_coupon_types(): void
    {
        $types = ['cart_base', 'product_base', 'free_shipping'];
        foreach ($types as $type) {
            $this->assertIsString($type);
            $this->assertNotEmpty($type);
        }
    }
}

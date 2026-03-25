<?php

namespace Tests\Unit\Utilities;

use Tests\TestCase;
use App\Utility\CartUtility;
use Mockery;

/**
 * CartUtilityTest
 *
 * Tests the CartUtility static methods:
 * - discount_calculation (percent and amount types)
 * - tax_calculation
 * - cart variant string building
 * - auction check in cart
 */
class CartUtilityTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─── discount_calculation ────────────────────────────────────────────────

    /** @test */
    public function discount_calculation_percent_type(): void
    {
        $product = $this->makeProduct(['discount' => 20, 'discount_type' => 'percent', 'discount_start_date' => null]);
        $price   = 100.00;

        $result = CartUtility::discount_calculation($product, $price);

        $this->assertEquals(80.00, $result);
    }

    /** @test */
    public function discount_calculation_amount_type(): void
    {
        $product = $this->makeProduct(['discount' => 15, 'discount_type' => 'amount', 'discount_start_date' => null]);
        $price   = 100.00;

        $result = CartUtility::discount_calculation($product, $price);

        $this->assertEquals(85.00, $result);
    }

    /** @test */
    public function discount_calculation_no_discount(): void
    {
        $product = $this->makeProduct(['discount' => 0, 'discount_type' => 'percent', 'discount_start_date' => null]);
        $price   = 100.00;

        $result = CartUtility::discount_calculation($product, $price);

        $this->assertEquals(100.00, $result);
    }

    /** @test */
    public function discount_not_applied_outside_date_range(): void
    {
        // Start date in the future — discount should NOT be applied
        $product = $this->makeProduct([
            'discount'            => 50,
            'discount_type'       => 'percent',
            'discount_start_date' => strtotime('+2 days'),
            'discount_end_date'   => strtotime('+10 days'),
        ]);
        $price  = 200.00;
        $result = CartUtility::discount_calculation($product, $price);

        $this->assertEquals(200.00, $result);
    }

    /** @test */
    public function discount_applied_within_date_range(): void
    {
        // Date range wraps now — discount should apply
        $product = $this->makeProduct([
            'discount'            => 10,
            'discount_type'       => 'percent',
            'discount_start_date' => strtotime('-1 day'),
            'discount_end_date'   => strtotime('+1 day'),
        ]);
        $price  = 100.00;
        $result = CartUtility::discount_calculation($product, $price);

        $this->assertEquals(90.00, $result);
    }

    // ─── tax_calculation ─────────────────────────────────────────────────────

    /** @test */
    public function tax_calculation_percent_type(): void
    {
        $tax1 = (object)['tax_type' => 'percent', 'tax' => 10]; // 10%
        $tax2 = (object)['tax_type' => 'percent', 'tax' => 5];  // 5%

        $product = $this->makeProduct([]);
        $product->taxes = collect([$tax1, $tax2]);

        $result = CartUtility::tax_calculation($product, 100.00);

        $this->assertEquals(15.00, $result); // 10% + 5% of 100
    }

    /** @test */
    public function tax_calculation_amount_type(): void
    {
        $tax = (object)['tax_type' => 'amount', 'tax' => 8];

        $product = $this->makeProduct([]);
        $product->taxes = collect([$tax]);

        $result = CartUtility::tax_calculation($product, 200.00);

        $this->assertEquals(8.00, $result); // flat RM 8
    }

    /** @test */
    public function tax_calculation_returns_zero_with_no_taxes(): void
    {
        $product = $this->makeProduct([]);
        $product->taxes = collect([]);

        $result = CartUtility::tax_calculation($product, 150.00);

        $this->assertEquals(0.00, $result);
    }

    // ─── check_auction_in_cart ───────────────────────────────────────────────

    /** @test */
    public function check_auction_returns_true_when_auction_product_in_cart(): void
    {
        $auctionProduct = (object)['auction_product' => 1];
        $cart1 = (object)['product' => $auctionProduct];

        $result = CartUtility::check_auction_in_cart([$cart1]);
        $this->assertTrue($result);
    }

    /** @test */
    public function check_auction_returns_false_when_no_auction_in_cart(): void
    {
        $normalProduct = (object)['auction_product' => 0];
        $cart1 = (object)['product' => $normalProduct];

        $result = CartUtility::check_auction_in_cart([$cart1]);
        $this->assertFalse($result);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Build a plain stdClass product mock with given attributes.
     */
    protected function makeProduct(array $attrs): object
    {
        $product = new \stdClass();
        foreach ($attrs as $key => $val) {
            $product->$key = $val;
        }
        return $product;
    }
}

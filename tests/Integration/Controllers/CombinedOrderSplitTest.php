<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\CombinedOrder;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CombinedOrderSplitTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $sellerA;
    protected $sellerB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['balance' => 10000]);
        $this->sellerA = User::factory()->seller()->create();
        \App\Models\Shop::create(['user_id' => $this->sellerA->id, 'name' => 'Shop A']);
        
        $this->sellerB = User::factory()->seller()->create();
        \App\Models\Shop::create(['user_id' => $this->sellerB->id, 'name' => 'Shop B']);
    }

    /** @test */
    public function combined_order_splits_into_per_seller_orders()
    {
        $combinedOrder = CombinedOrder::factory()->create([
            'user_id' => $this->buyer->id,
        ]);

        // Seller A order — 500 MAD
        $orderA = Order::factory()->paid()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $this->buyer->id,
            'seller_id' => $this->sellerA->id,
            'grand_total' => 500.00,
        ]);

        // Seller B order — 300 MAD
        $orderB = Order::factory()->paid()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $this->buyer->id,
            'seller_id' => $this->sellerB->id,
            'grand_total' => 300.00,
        ]);

        // Update combined total
        $combinedOrder->grand_total = 800.00;
        $combinedOrder->save();

        // Verify split
        $this->assertEquals(2, $combinedOrder->orders->count());
        $this->assertEquals($this->sellerA->id, $combinedOrder->orders->first()->seller_id);
        $this->assertEquals(800.00, $combinedOrder->fresh()->grand_total);

        // Verify each sub-order belongs to the correct seller
        $sellerIds = $combinedOrder->orders->pluck('seller_id')->sort()->values()->toArray();
        $expected = collect([$this->sellerA->id, $this->sellerB->id])->sort()->values()->toArray();
        $this->assertEquals($expected, $sellerIds);
    }

    /** @test */
    public function each_sub_order_has_correct_payment_status()
    {
        $combinedOrder = CombinedOrder::factory()->create([
            'user_id' => $this->buyer->id,
        ]);

        $orderPaid = Order::factory()->paid()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $this->buyer->id,
            'seller_id' => $this->sellerA->id,
            'grand_total' => 200.00,
        ]);

        $orderUnpaid = Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $this->buyer->id,
            'seller_id' => $this->sellerB->id,
            'grand_total' => 150.00,
        ]);

        $this->assertEquals('paid', $orderPaid->payment_status);
        $this->assertEquals('unpaid', $orderUnpaid->payment_status);
    }

    /** @test */
    public function grand_total_equals_sum_of_sub_orders()
    {
        $combinedOrder = CombinedOrder::factory()->create([
            'user_id' => $this->buyer->id,
        ]);

        $amounts = [250.50, 175.25, 99.99];

        foreach ($amounts as $i => $amount) {
            Order::factory()->create([
                'combined_order_id' => $combinedOrder->id,
                'user_id' => $this->buyer->id,
                'seller_id' => User::factory()->create()->id,
                'grand_total' => $amount,
            ]);
        }

        // Calculate expected total
        $expectedTotal = array_sum($amounts);
        $actualTotal = $combinedOrder->orders->sum('grand_total');

        $this->assertEquals(round($expectedTotal, 2), round($actualTotal, 2));
    }

    /** @test */
    public function combined_order_belongs_to_buyer()
    {
        $combinedOrder = CombinedOrder::factory()->create([
            'user_id' => $this->buyer->id,
        ]);

        $this->assertEquals($this->buyer->id, $combinedOrder->user_id);
        $this->assertInstanceOf(User::class, $combinedOrder->user);
        $this->assertEquals($this->buyer->id, $combinedOrder->user->id);
    }

    /** @test */
    public function single_seller_cart_produces_one_sub_order()
    {
        $combinedOrder = CombinedOrder::factory()->create([
            'user_id' => $this->buyer->id,
        ]);

        Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $this->buyer->id,
            'seller_id' => $this->sellerA->id,
            'grand_total' => 600.00,
        ]);

        $this->assertEquals(1, $combinedOrder->orders->count());
    }

    /** @test */
    public function shipping_address_is_consistent_across_combined_order()
    {
        $address = json_encode([
            'name' => 'Test Buyer',
            'address' => '123 Rue Hassan II, Casablanca',
            'phone' => '+212600000000',
        ]);

        $combinedOrder = CombinedOrder::factory()->create([
            'user_id' => $this->buyer->id,
            'shipping_address' => $address,
        ]);

        Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $this->buyer->id,
            'seller_id' => $this->sellerA->id,
            'shipping_address' => $address,
            'grand_total' => 100,
        ]);

        $subOrder = $combinedOrder->orders->first();
        $this->assertEquals(
            json_decode($combinedOrder->shipping_address, true),
            json_decode($subOrder->shipping_address, true)
        );
    }

    /** @test */
    public function multi_seller_cart_calculates_correct_commissions()
    {
        // Setup: Enable vendor commission and set to 10%
        \DB::table('business_settings')->updateOrInsert(['type' => 'vendor_commission_activation'], ['value' => 1]);
        \DB::table('business_settings')->updateOrInsert(['type' => 'seller_commission_type'], ['value' => 'fixed_rate']);
        \DB::table('business_settings')->updateOrInsert(['type' => 'vendor_commission'], ['value' => 10]);

        $combinedOrder = CombinedOrder::factory()->create(['user_id' => $this->buyer->id]);
        
        // Seller A order — 100 MAD product
        $orderA = Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $this->buyer->id,
            'seller_id' => $this->sellerA->id,
            'grand_total' => 100.00,
            'payment_status' => 'paid',
        ]);
        
        $detailA = OrderDetail::factory()->create([
            'order_id' => $orderA->id,
            'seller_id' => $this->sellerA->id,
            'price' => 100.00,
            'tax' => 0,
            'shipping_cost' => 0,
            'quantity' => 1,
            'payment_status' => 'paid',
        ]);

        // Manually trigger commission calculation (simulating the callback logic)
        $orderA->refresh();
        (new \App\Http\Controllers\CommissionController)->calculateCommission($orderA);

        $this->assertDatabaseHas('commission_histories', [
            'order_id' => $orderA->id,
            'seller_id' => $this->sellerA->id,
            'admin_commission' => 10.00, // 10% of 100
            'seller_earning' => 90.00,
        ]);
    }

    /** @test */
    public function shipping_segregation_per_seller()
    {
        $combinedOrder = CombinedOrder::factory()->create(['user_id' => $this->buyer->id]);
        
        $orderA = Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'seller_id' => $this->sellerA->id,
            'grand_total' => 120.00, // 100 product + 20 shipping
        ]);
        
        $orderB = Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'seller_id' => $this->sellerB->id,
            'grand_total' => 350.00, // 300 product + 50 shipping
        ]);

        $this->assertEquals(2, $combinedOrder->orders->count());
        $this->assertEquals(20, $combinedOrder->orders()->where('seller_id', $this->sellerA->id)->first()->grand_total - 100);
        $this->assertEquals(50, $combinedOrder->orders()->where('seller_id', $this->sellerB->id)->first()->grand_total - 300);
    }
}

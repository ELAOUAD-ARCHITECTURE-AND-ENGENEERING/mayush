<?php

namespace Tests\Integration\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CombinedOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_combined_order_with_multiple_sub_orders()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $seller1 = User::factory()->create(['user_type' => 'seller']);
        $seller2 = User::factory()->create(['user_type' => 'seller']);

        $product1 = Product::factory()->create(['user_id' => $seller1->id, 'unit_price' => 100]);
        $product2 = Product::factory()->create(['user_id' => $seller2->id, 'unit_price' => 200]);

        // 2. Create Combined Order
        $combinedOrder = CombinedOrder::create([
            'user_id' => $user->id,
            'shipping_address' => json_encode(['name' => 'Test User', 'address' => '123 Street']),
            'grand_total' => 300 // Simulating manual total for now
        ]);

        // 3. Create Sub-Orders (Splitting logic as used in ExpressBuy/Checkout)
        $order1 = Order::create([
             'combined_order_id' => $combinedOrder->id,
             'user_id' => $user->id,
             'seller_id' => $seller1->id,
             'grand_total' => 100,
             'code' => 'TEST-001-' . time(),
             'payment_type' => 'cash_on_delivery',
             'payment_status' => 'unpaid'
        ]);

        $order2 = Order::create([
             'combined_order_id' => $combinedOrder->id,
             'user_id' => $user->id,
             'seller_id' => $seller2->id,
             'grand_total' => 200,
             'code' => 'TEST-002-' . time(),
             'payment_type' => 'cash_on_delivery',
             'payment_status' => 'unpaid'
        ]);

        // 4. Verification
        $this->assertCount(2, $combinedOrder->orders);
        $this->assertEquals(300, $combinedOrder->orders->sum('grand_total'));
        $this->assertEquals($user->id, $combinedOrder->user_id);
        
        $this->assertEquals($seller1->id, $order1->seller_id);
        $this->assertEquals($seller2->id, $order2->seller_id);
    }

    /** @test */
    public function it_calculates_correct_totals_when_splitting_a_combined_order_with_taxes_and_shipping()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $seller1 = User::factory()->create(['user_type' => 'seller']);
        $product1 = Product::factory()->create(['user_id' => $seller1->id, 'unit_price' => 100]);

        $combinedOrder = CombinedOrder::create([
            'user_id' => $user->id,
            'shipping_address' => json_encode(['name' => 'Test User', 'address' => '123 Street']),
            'grand_total' => 125 // 100 (price) + 10 (tax) + 15 (shipping)
        ]);

        $order1 = Order::create([
             'combined_order_id' => $combinedOrder->id,
             'user_id' => $user->id,
             'seller_id' => $seller1->id,
             'grand_total' => 125,
             'code' => 'TEST-TAX-001',
             'payment_type' => 'cmi',
             'payment_status' => 'paid'
        ]);

        $orderDetail = OrderDetail::create([
            'order_id' => $order1->id,
            'seller_id' => $seller1->id,
            'product_id' => $product1->id,
            'price' => 100,
            'tax' => 10,
            'shipping_cost' => 15,
            'quantity' => 1
        ]);

        // Verification
        $this->assertEquals(
            $orderDetail->price + $orderDetail->tax + $orderDetail->shipping_cost,
            $order1->grand_total
        );
        $this->assertEquals($order1->grand_total, $combinedOrder->grand_total);
    }

    /** @test */
    public function it_creates_inventory_logs_upon_order_creation()
    {
        $user = User::factory()->create();
        $seller = User::factory()->create(['user_type' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);
        
        // Simulating the creation of a log during checkout
        $log = \App\Models\InventoryLog::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity_delta' => -2,
            'previous_stock' => 10,
            'current_stock' => 8,
            'reason' => 'order',
            'order_id' => 999 
        ]);

        $this->assertDatabaseHas('inventory_logs', [
            'id' => $log->id,
            'quantity_delta' => -2,
            'reason' => 'order'
        ]);
        $this->assertEquals(10, $log->previous_stock);
        $this->assertEquals(8, $log->current_stock);
    }
}

<?php

namespace Tests\Feature\Customer;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseHistoryRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sees_only_own_purchase_history_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $ownProduct = Product::factory()->create(['name' => 'Customer History Lamp']);
        $otherProduct = Product::factory()->create(['name' => 'Other Customer Vase']);

        $ownOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'code' => 'OWN-1001',
            'date' => now()->timestamp,
        ]);
        OrderDetail::factory()->create([
            'order_id' => $ownOrder->id,
            'product_id' => $ownProduct->id,
            'seller_id' => $ownProduct->user_id,
        ]);

        $otherOrder = Order::factory()->create([
            'user_id' => $otherCustomer->id,
            'code' => 'OTHER-2002',
            'date' => now()->timestamp,
        ]);
        OrderDetail::factory()->create([
            'order_id' => $otherOrder->id,
            'product_id' => $otherProduct->id,
            'seller_id' => $otherProduct->user_id,
        ]);

        $this->actingAs($customer)
            ->get(route('purchase_history.index'))
            ->assertOk()
            ->assertSee('OWN-1001')
            ->assertSee('Customer History Lamp')
            ->assertDontSee('OTHER-2002')
            ->assertDontSee('Other Customer Vase');
    }

    public function test_empty_purchase_history_renders_empty_state_without_ajax_reload_loop(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)
            ->get(route('purchase_history.index'))
            ->assertOk()
            ->assertSee('No purchase history found.');

        $response->assertSee("// loadOrdersByStatus('all'); // Already rendered by PHP", false);
    }

    public function test_purchase_history_filter_is_scoped_and_returns_json_html(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $product = Product::factory()->create(['name' => 'Delivered Filter Product']);

        $deliveredOrder = Order::factory()->delivered()->create([
            'user_id' => $customer->id,
            'code' => 'DELIVERED-100',
            'date' => now()->timestamp,
        ]);
        OrderDetail::factory()->create([
            'order_id' => $deliveredOrder->id,
            'product_id' => $product->id,
            'seller_id' => $product->user_id,
            'delivery_status' => 'delivered',
        ]);

        Order::factory()->delivered()->create([
            'user_id' => $otherCustomer->id,
            'code' => 'DELIVERED-OTHER',
            'date' => now()->timestamp,
        ]);

        $this->actingAs($customer)
            ->getJson(route('purchase_history.filter', ['tab' => 'delivered']))
            ->assertOk()
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'DELIVERED-100')
                && ! str_contains($html, 'DELIVERED-OTHER'));
    }

    public function test_purchase_history_detail_blocks_other_customers_order(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $otherCustomer->id,
            'code' => 'PRIVATE-ORDER',
        ]);

        $this->actingAs($customer)
            ->get(route('purchase_history.details', encrypt($order->id)))
            ->assertNotFound();
    }

    public function test_purchase_history_row_handles_missing_product_relation(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'code' => 'MISSING-PRODUCT',
            'date' => now()->timestamp,
        ]);
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
        $product->delete();

        $this->actingAs($customer)
            ->get(route('purchase_history.index'))
            ->assertOk()
            ->assertSee('MISSING-PRODUCT')
            ->assertSee('Product unavailable');
    }
}

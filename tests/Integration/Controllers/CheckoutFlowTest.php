<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $address;

    protected function setUp(): void
    {
        parent::setUp();
        
        \DB::table('countries')->insert(['id' => 1, 'name' => 'Test Country', 'status' => 1, 'code' => 'TC', 'zone_id' => 1]);
        \DB::table('states')->insert(['id' => 1, 'name' => 'Test State', 'country_id' => 1, 'status' => 1]);
        \DB::table('cities')->insert(['id' => 1, 'name' => 'Test City', 'state_id' => 1, 'country_id' => 1, 'status' => 1]);
        
        $this->user = User::factory()->create(['balance' => 1000]);
        $seller = User::factory()->create();
        
        $this->product = Product::factory()->create([
            'unit_price' => 100,
            'discount' => 10,
            'discount_type' => 'percent',
            'tax' => 5,
            'tax_type' => 'percent',
            'shipping_type' => 'flat_rate',
            'flat_shipping_cost' => 20,
            'is_quantity_multiplied' => 1,
            'user_id' => $seller->id,
            'published' => 1,
            'approved' => 1,
            'min_qty' => 1
        ]);
        
        $this->product->stocks()->create([
            'variant' => '',
            'price' => 100,
            'qty' => 50
        ]);

        $this->address = Address::create([
            'user_id' => $this->user->id,
            'address' => 'Test Address, 123 Main St',
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1,
            'phone' => '+123456789'
        ]);
    }

    /** @test */
    public function e2e_checkout_success_flow()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);

        // 1. Add to Cart (Mock equivalent directly in DB for simplicity of integration test)
        $cart = Cart::create([
            'owner_id' => $this->user->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'price' => 90, // 100 - 10% discount
            'tax' => 4.5, // 5% of 90
            'shipping_cost' => 20,
            'quantity' => 2,
            'address_id' => $this->address->id,
            'billing_address' => $this->address->id,
            'variation' => '',
        ]);
        
        $cart->status = 1;
        $cart->save();

        // 2. Checkout Store (Create CombinedOrder)
        // This simulates checkout.blade.php submission
        $response = $this->post(route('payment.checkout'), [
            'address_id' => $this->address->id,
            'payment_option' => 'cash_on_delivery'
        ]);

        $response->dump();
        $response->assertStatus(302);
        
        $combinedOrderId = Session::get('combined_order_id');
        $this->assertNotNull($combinedOrderId);

        // 3. Complete checkout
        $response = $this->post(route('checkout.checkout_done'), [
            'combined_order_id' => $combinedOrderId,
            'payment_type' => 'cash_on_delivery',
            'payment_status' => 'unpaid'
        ]);

        // Verify order creation
        $this->assertEquals(0, Cart::where('user_id', $this->user->id)->count());
        $this->assertEquals(1, \App\Models\CombinedOrder::where('user_id', $this->user->id)->count());
        $this->assertEquals(1, \App\Models\Order::where('user_id', $this->user->id)->count());
        
        $order = \App\Models\Order::first();
        // Grand Total: (90 + 4.5) * 2 + (20 * 2) = 189 + 40 = 229
        $this->assertEquals(229, $order->grand_total);
    }
}

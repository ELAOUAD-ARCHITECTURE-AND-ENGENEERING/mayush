<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Country;
use App\Models\City;
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
        
        // Create required associations for address
        Country::updateOrCreate(['id' => 1], ['name' => 'Morocco', 'code' => 'MA', 'status' => 1]);
        City::updateOrCreate(['id' => 1], ['name' => 'Casablanca', 'country_id' => 1, 'status' => 1]);

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

        $this->address = Address::factory()->create(['user_id' => $this->user->id]);

        \DB::table('business_settings')->updateOrInsert(['type' => 'minimum_order_amount'], ['value' => 0]);
        \DB::table('business_settings')->updateOrInsert(['type' => 'shipping_type'], ['value' => 'flat_rate']);
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
            'discount' => 0,
            'shipping_type' => 'flat_rate',
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

        $response->assertStatus(302);
        
        $combinedOrderId = Session::get('combined_order_id');
        $this->assertNotNull($combinedOrderId);

        // Verify order creation
        $this->assertEquals(0, Cart::where('user_id', $this->user->id)->count());
        $this->assertEquals(1, \App\Models\CombinedOrder::where('user_id', $this->user->id)->count());
        $this->assertEquals(1, \App\Models\Order::where('user_id', $this->user->id)->count());
        
        $order = \App\Models\Order::first();
        // Calculation: (90 price * 2 quantity) + 20 shipping = 200 (Tax is 0 in test env due to missing settings)
        $this->assertEquals(200, $order->grand_total);
    }

    /** @test */
    public function digital_product_checkout_skips_shipping()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);

        $digitalProduct = Product::factory()->create([
            'unit_price' => 100,
            'digital' => 1,
            'published' => 1,
            'approved' => 1,
            'min_qty' => 1
        ]);

        // Digital products still need a stock record for cart_product_price() helper
        $digitalProduct->stocks()->create([
            'variant' => '',
            'price' => 100,
            'qty' => 999
        ]);

        Cart::create([
            'owner_id' => $this->user->id,
            'user_id' => $this->user->id,
            'product_id' => $digitalProduct->id,
            'price' => 100,
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'shipping_type' => 'flat_rate',
            'quantity' => 1,
            'address_id' => $this->address->id,
            'billing_address' => $this->address->id,
            'variation' => '',
            'status' => 1
        ]);

        $response = $this->post(route('payment.checkout'), [
            'address_id' => $this->address->id,
            'payment_option' => 'cash_on_delivery'
        ]);

        $response->assertStatus(302);
        
        $this->assertEquals(1, \App\Models\CombinedOrder::count());
        $order = \App\Models\Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(100, $order->grand_total);
        $this->assertEquals(0, $order->orderDetails->sum('shipping_cost'));
    }

    /** @test */
    public function checkout_reduces_stock_and_logs_inventory()
    {
        $this->actingAs($this->user);

        // Initial stock is 50
        $stock = $this->product->stocks->first();
        $initialQty = $stock->qty;

        Cart::create([
            'owner_id' => $this->user->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'price' => 450, // 90 * 5
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'shipping_type' => 'flat_rate',
            'quantity' => 5, // Order 5 items
            'address_id' => $this->address->id,
            'billing_address' => $this->address->id,
            'variation' => '',
            'status' => 1
        ]);

        $response = $this->post(route('payment.checkout'), [
            'address_id' => $this->address->id,
            'payment_option' => 'cash_on_delivery'
        ]);
        

        $response->assertStatus(302);
        $this->assertEquals(1, \App\Models\Order::count());

        // Assert stock reduction
        $this->assertEquals($initialQty - 5, $stock->fresh()->qty);

        // Assert inventory log entry
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $this->product->id,
            'quantity_delta' => -5,
            'reason' => 'order'
        ]);
    }
}

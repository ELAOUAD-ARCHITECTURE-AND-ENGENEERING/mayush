<?php

namespace Tests\Integration\API;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiV2OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        // Create admin for notifications
        User::factory()->create(['user_type' => 'admin']);

        $this->product = Product::factory()->create(['published' => 1, 'approved' => 1]);
        
        \App\Models\ProductStock::create([
            'product_id' => $this->product->id,
            'variant' => '',
            'price' => 100,
            'qty' => 100,
            'sku' => 'TEST-SKU-ORDER'
        ]);

        // Setup country and city for address
        $country = \App\Models\Country::create(['name' => 'Morocco', 'code' => 'MA', 'status' => 1]);
        $city = \App\Models\City::create(['name' => 'Casablanca', 'country_id' => $country->id, 'status' => 1]);

        $this->country_id = $country->id;
        $this->city_id = $city->id;

        // Setup necessary business settings for order placement
        BusinessSetting::factory()->create(['type' => 'minimum_order_amount_check', 'value' => '0']);
    }

    /** @test */
    public function it_can_place_an_order_via_api()
    {
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id
        ]);
        
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'address_id' => $address->id,
            'quantity' => 1,
            'price' => 100,
            'tax' => 0,
            'shipping_cost' => 10
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v2/order/store', [
            'payment_type' => 'cash_on_delivery'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['result' => true]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_type' => 'cash_on_delivery'
        ]);
        
        // Cart should be empty after order
        $this->assertDatabaseMissing('carts', ['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_can_view_purchase_history()
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v2/purchase-history');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_cannot_view_another_users_order_details()
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v2/purchase-history-details/' . $order->id);

        // Based on implementation, it returns 200 but with empty data if not owned
        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }

    /** @test */
    public function user_can_cancel_their_own_unpaid_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/v2/order/cancel/' . $order->id);

        $response->assertStatus(200)
                 ->assertJson(['result' => true]);

        $this->assertEquals('cancelled', $order->fresh()->delivery_status);
    }
    /** @test */
    public function it_rejects_unsupported_payment_methods_under_cmi_policy()
    {
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id
        ]);
        
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'address_id' => $address->id,
            'quantity' => 1,
            'price' => 100
        ]);
        
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v2/order/store', [
            'payment_type' => 'paypal'
        ]);

        $response->assertStatus(422)
                 ->assertJson(['result' => false, 'message' => 'Unsupported payment method.']);
    }
}

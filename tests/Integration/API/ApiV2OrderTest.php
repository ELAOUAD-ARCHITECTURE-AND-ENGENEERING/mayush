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
        $this->product = Product::factory()->create(['published' => 1, 'approved' => 1]);
        
        // Setup necessary business settings for order placement
        BusinessSetting::factory()->create(['type' => 'minimum_order_amount_check', 'value' => '0']);
    }

    /** @test */
    public function it_can_place_an_order_via_api()
    {
        $address = Address::factory()->create(['user_id' => $this->user->id]);
        
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

        // Based on implementation, it might return 404 or just ignore if filtered by auth user
        // OrderController::details likely does: Order::where('id', $id)->where('user_id', auth()->user()->id)->first()
        $response->assertStatus(404); 
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
                 ->assertJson(['success' => true]);

        $this->assertEquals('cancelled', $order->fresh()->delivery_status);
    }
}

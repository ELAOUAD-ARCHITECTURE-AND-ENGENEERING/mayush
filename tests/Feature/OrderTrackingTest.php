<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderTrackingHistory;
use App\Models\User;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup basic settings if needed
    }

    /** @test */
    public function buyer_can_view_own_order_tracking()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $order = Order::factory()->create(['user_id' => $user->id]);
        OrderTrackingHistory::factory()->count(3)->create(['order_id' => $order->id]);

        $this->actingAs($user);
        $response = $this->get(route('orders.tracking.show', encrypt($order->id)));

        $response->assertStatus(200);
        $response->assertViewHas('order', $order);
        $response->assertSee($order->code);
    }

    /** @test */
    public function buyer_cannot_view_other_order_tracking()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $otherUser = User::factory()->create(['user_type' => 'customer']);
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);
        $response = $this->get(route('orders.tracking.show', encrypt($order->id)));

        $response->assertStatus(403);
    }

    /** @test */
    public function seller_can_view_order_with_their_products()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $order = Order::factory()->create(['seller_id' => $seller->id]);

        $this->actingAs($seller);
        $response = $this->get(route('orders.tracking.show', encrypt($order->id)));

        $response->assertStatus(200);
    }

    /** @test */
    public function seller_cannot_view_other_seller_order()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $otherSeller = User::factory()->create(['user_type' => 'seller']);
        $order = Order::factory()->create(['seller_id' => $otherSeller->id]);

        $this->actingAs($seller);
        $response = $this->get(route('orders.tracking.show', encrypt($order->id)));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_any_order_tracking()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $order = Order::factory()->create();

        $this->actingAs($admin);
        $response = $this->get(route('orders.tracking.show', encrypt($order->id)));

        $response->assertStatus(200);
    }

    /** @test */
    public function webhook_ingestion_creates_history_record()
    {
        $order = Order::factory()->create(['tracking_code' => 'TRACK123']);

        $response = $this->postJson('/api/v2/tracking/webhook', [
            'tracking_code' => 'TRACK123'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('order_tracking_histories', [
            'order_id' => $order->id,
        ]);
    }

    /** @test */
    public function manual_sync_creates_history_record()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'tracking_code' => 'SYNC123'
        ]);

        $this->actingAs($user);
        $response = $this->get(route('orders.tracking.sync', encrypt($order->id)));

        $response->assertStatus(302); // Redirect back
        
        $this->assertDatabaseHas('order_tracking_histories', [
            'order_id' => $order->id,
        ]);
    }
}

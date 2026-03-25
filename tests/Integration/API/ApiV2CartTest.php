<?php

namespace Tests\Integration\API;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiV2CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = Product::factory()->create(['published' => 1, 'approved' => 1, 'min_qty' => 1]);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_add_product_to_cart_for_authenticated_user()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v2/carts/add', [
            'id' => $this->product->id,
            'user_id' => $this->user->id,
            'quantity' => 1,
            'variant' => ''
        ]);

        $response->assertStatus(200)
                 ->assertJson(['result' => true]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    /** @test */
    public function it_can_add_product_to_cart_for_guest_user()
    {
        $tempUserId = 'temp_' . rand(100, 999);
        $response = $this->postJson('/api/v2/carts/add', [
            'id' => $this->product->id,
            'temp_user_id' => $tempUserId,
            'quantity' => 1,
            'variant' => ''
        ]);

        $response->assertStatus(200)
                 ->assertJson(['result' => true]);

        $this->assertDatabaseHas('carts', [
            'temp_user_id' => $tempUserId,
            'product_id' => $this->product->id
        ]);
    }

    /** @test */
    public function it_can_list_cart_items()
    {
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v2/carts', [
            'user_id' => $this->user->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['product_id' => (int)$this->product->id]);
    }

    /** @test */
    public function it_can_update_cart_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v2/carts/change-quantity', [
            'id' => $cart->id,
            'quantity' => 5
        ]);

        $response->assertStatus(200)
                 ->assertJson(['result' => true]);

        $this->assertEquals(5, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_can_remove_item_from_cart()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/v2/carts/' . $cart->id);

        $response->assertStatus(200)
                 ->assertJson(['result' => true]);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }
}

<?php

namespace Tests\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IdorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_cannot_view_another_users_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user1->id]);

        // User 2 trying to view User 1's order
        $response = $this->actingAs($user2)->get(route('purchase_history.details', encrypt($order->id)));

        // Should return 403 Forbidden or 404 Not Found (to not leak existence)
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /** @test */
    public function guest_cannot_view_any_order()
    {
        $order = Order::factory()->create();

        $response = $this->get(route('purchase_history.details', encrypt($order->id)));

        $response->assertRedirect(route('user.login'));
    }

    /** @test */
    public function user_cannot_update_another_users_profile()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 2 trying to update User 1's profile
        $response = $this->actingAs($user2)->post(route('user.profile.update'), [
            'id' => $user1->id,
            'name' => 'Hacker Name'
        ]);

        // Should not update User 1's data
        $this->assertNotEquals('Hacker Name', $user1->fresh()->name);
    }
}

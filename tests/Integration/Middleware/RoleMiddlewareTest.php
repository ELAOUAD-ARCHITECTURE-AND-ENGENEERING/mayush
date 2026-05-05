<?php

namespace Tests\Integration\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect();
    }

    /** @test */
    public function customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertStatus(404);
    }

    /** @test */
    public function admin_can_reach_admin_dashboard_route(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function guest_cannot_access_seller_dashboard(): void
    {
        $this->get(route('seller.dashboard'))->assertStatus(404);
    }

    /** @test */
    public function customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('seller.dashboard'))
            ->assertStatus(404);
    }

    /** @test */
    public function guest_cannot_access_purchase_history(): void
    {
        $this->get(route('purchase_history.index'))->assertRedirect();
    }

    /** @test */
    public function customer_is_not_redirected_to_login_for_purchase_history(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('purchase_history.index'));

        $this->assertStringNotContainsString('login', $response->headers->get('Location', ''));
    }
}

<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SeedsAppConfigs;

/**
 * RouteContractTest
 *
 * Verifies that key application routes remain reachable and mapped correctly
 * as per the route contracts.
 */
class RouteContractTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function admin_dashboard_route_contract()
    {
        $admin = User::factory()->admin()->create();
        
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        
        // Assert reachable (not 404)
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function seller_dashboard_route_contract()
    {
        $seller = User::factory()->seller()->create();
        
        $response = $this->actingAs($seller)->get(route('seller.dashboard'));
        
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function purchase_history_route_contract()
    {
        $customer = User::factory()->customer()->create();
        
        $response = $this->actingAs($customer)->get(route('purchase_history.index'));
        
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function review_store_route_contract()
    {
        $this->assertTrue(\Route::has('reviews.store'), 'reviews.store route name must exist');
    }
}

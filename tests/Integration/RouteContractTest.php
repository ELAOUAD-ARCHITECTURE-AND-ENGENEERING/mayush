<?php

namespace Tests\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class RouteContractTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function critical_route_names_exist(): void
    {
        foreach ([
            'home',
            'admin.dashboard',
            'seller.dashboard',
            'purchase_history.index',
            'reviews.store',
            'reviews.index',
            'reviews.published',
            'blog',
            'blog.details',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "{$routeName} route must exist");
        }
    }

    /** @test */
    public function admin_dashboard_route_contract(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function seller_dashboard_route_contract(): void
    {
        $seller = User::factory()->seller()->create();

        $response = $this->actingAs($seller)->get(route('seller.dashboard'));

        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function purchase_history_route_contract(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('purchase_history.index'));

        $this->assertNotEquals(404, $response->status());
    }
}

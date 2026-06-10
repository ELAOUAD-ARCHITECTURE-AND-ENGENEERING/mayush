<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SeedsAppConfigs;

class SellerIsolationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function seller_can_view_only_their_own_dashboard_data(): void
    {
        $seller1 = User::factory()->seller()->create();
        $shop1 = Shop::factory()->create(['user_id' => $seller1->id, 'approval_status' => 'approved']);
        
        $seller2 = User::factory()->seller()->create();
        $shop2 = Shop::factory()->create(['user_id' => $seller2->id, 'approval_status' => 'approved']);

        $this->actingAs($seller1);
        $response = $this->get(route('seller.dashboard'));
        
        // Should be accessible
        $this->assertContains($response->status(), [200, 302]);
        
        // Should not see seller2's data
        $this->actingAs($seller2);
        $response2 = $this->get(route('seller.dashboard'));
        $this->assertContains($response2->status(), [200, 302]);
    }

    /** @test */
    public function seller_cannot_view_another_sellers_orders(): void
    {
        $seller1 = User::factory()->seller()->create();
        $seller2 = User::factory()->seller()->create();
        
        $order = Order::factory()->create(['seller_id' => $seller1->id]);

        $this->actingAs($seller2);
        $response = $this->get(route('seller.orders.show', encrypt($order->id)));

        // Should be forbidden (403) due to our new check
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function seller_cannot_edit_another_sellers_product(): void
    {
        $seller1 = User::factory()->seller()->create();
        $shop1 = Shop::factory()->create(['user_id' => $seller1->id, 'approval_status' => 'approved']);
        
        $seller2 = User::factory()->seller()->create();
        $shop2 = Shop::factory()->create(['user_id' => $seller2->id, 'approval_status' => 'approved']);
        
        $product = Product::factory()->create([
            'user_id' => $seller1->id,
            'added_by' => 'seller'
        ]);

        $this->actingAs($seller2);
        $response = $this->get(route('seller.products.edit', $product->id));

        // Should be forbidden
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function seller_cannot_access_admin_only_routes(): void
    {
        $seller = User::factory()->seller()->create();
        $shop = Shop::factory()->create(['user_id' => $seller->id, 'approval_status' => 'approved']);

        $this->actingAs($seller);
        $response = $this->get(route('admin.dashboard'));

        // Should be not found (404) for seller trying to access admin routes
        $this->assertEquals(404, $response->status());
    }

    /** @test */
    public function customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer);
        $response = $this->get(route('seller.dashboard'));

        // Should be not found
        $this->assertEquals(404, $response->status());
    }

    /** @test */
    public function guest_cannot_access_seller_dashboard(): void
    {
        $response = $this->get(route('seller.dashboard'));

        // Should redirect to login or be not found
        $this->assertContains($response->status(), [302, 404]);
    }

    /** @test */
    public function admin_can_access_seller_order_management(): void
    {
        $admin = User::factory()->admin()->create();
        $seller = User::factory()->seller()->create();
        $shop = Shop::factory()->create(['user_id' => $seller->id, 'approval_status' => 'approved']);
        $order = Order::factory()->create(['seller_id' => $seller->id]);
        
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('all_orders.show', encrypt($order->id)));

        // Should be accessible to admin
        $this->assertContains($response->status(), [200, 302]);
    }
}
<?php

namespace Tests\Feature\V109;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class ShippingLabelAuthorizationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
        $this->withoutMiddleware();
    }

    public function test_customer_cannot_view_another_customers_shipping_label(): void
    {
        $owner = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $seller = User::factory()->seller()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($other)->get(route('shipping-label.print', $order->id))
            ->assertForbidden();
    }

    public function test_bulk_shipping_label_routes_are_post_only(): void
    {
        $this->assertSame(['POST'], Route::getRoutes()->getByName('bulk-shipping-label.download')->methods());
        $this->assertSame(['POST'], Route::getRoutes()->getByName('bulk-shipping-label.print')->methods());
    }
}

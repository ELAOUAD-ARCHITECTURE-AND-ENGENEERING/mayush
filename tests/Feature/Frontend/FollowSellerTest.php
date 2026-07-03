<?php

namespace Tests\Feature\Frontend;

use App\Models\FollowSeller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class FollowSellerTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'vendor_system_activation'],
            ['value' => '1']
        );
    }

    public function test_guest_cannot_follow_seller(): void
    {
        $shop = Shop::factory()->create();

        $this->post(route('followed_seller.store'), ['id' => $shop->id])
            ->assertRedirect();

        $this->assertSame(0, FollowSeller::count());
    }

    public function test_customer_can_follow_seller(): void
    {
        $customer = User::factory()->customer()->create();
        $shop = Shop::factory()->create();

        $this->actingAs($customer)
            ->from(route('shop.visit', $shop->slug))
            ->post(route('followed_seller.store'), ['id' => $shop->id])
            ->assertRedirect(route('shop.visit', $shop->slug));

        $this->assertDatabaseHas('follow_sellers', [
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
        ]);
    }

    public function test_customer_cannot_duplicate_follow_same_seller(): void
    {
        $customer = User::factory()->customer()->create();
        $shop = Shop::factory()->create();

        $this->actingAs($customer)->post(route('followed_seller.store'), ['id' => $shop->id]);
        $this->actingAs($customer)->post(route('followed_seller.store'), ['id' => $shop->id]);

        $this->assertSame(1, FollowSeller::where('user_id', $customer->id)->where('shop_id', $shop->id)->count());
    }

    public function test_follow_route_only_accepts_post(): void
    {
        $route = Route::getRoutes()->getByName('followed_seller.store');

        $this->assertNotNull($route);
        $this->assertSame(['POST'], $route->methods());
    }

    public function test_seller_shop_view_uses_post_compatible_markup(): void
    {
        $markup = file_get_contents(resource_path('views/frontend/seller_shop.blade.php'));

        $this->assertStringContainsString('method="POST"', $markup);
        $this->assertStringContainsString("route('followed_seller.store')", $markup);
        $this->assertStringContainsString("route('followed_seller.remove')", $markup);
        $this->assertStringNotContainsString('route("followed_seller.store",', $markup);
    }
}

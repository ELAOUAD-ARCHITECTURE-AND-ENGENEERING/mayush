<?php

namespace Tests\Feature\Seller;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class SellerAnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_seller_analytics_dashboard_loads_with_empty_data(): void
    {
        $seller = $this->sellerWithShop();

        $this->actingAs($seller)
            ->get(route('seller.analytics.index'))
            ->assertOk()
            ->assertSee('Analytics');

        $this->actingAs($seller)
            ->getJson(route('seller.analytics.financial'))
            ->assertOk()
            ->assertJson([
                'gross_sales' => 0,
                'net_earnings' => 0,
                'commissions' => 0,
                'refunded' => 0,
                'order_count' => 0,
                'payout_ready' => 0,
            ]);
    }

    public function test_seller_top_products_are_scoped_to_authenticated_seller(): void
    {
        $seller = $this->sellerWithShop();
        $otherSeller = $this->sellerWithShop();

        $ownProduct = Product::factory()->create([
            'user_id' => $seller->id,
            'added_by' => 'seller',
            'name' => 'Own Analytics Product',
            'num_of_view' => 25,
            'num_of_sale' => 5,
        ]);
        $otherProduct = Product::factory()->create([
            'user_id' => $otherSeller->id,
            'added_by' => 'seller',
            'name' => 'Other Seller Product',
            'num_of_view' => 100,
            'num_of_sale' => 20,
        ]);

        Cart::factory()->create(['product_id' => $ownProduct->id]);
        Cart::factory()->create(['product_id' => $otherProduct->id]);

        $response = $this->actingAs($seller)
            ->getJson(route('seller.analytics.top_products'));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'product_id' => $ownProduct->id,
                'name' => 'Own Analytics Product',
                'views' => 25,
                'cart_adds' => 1,
                'sold' => 5,
            ]);

        $this->assertStringNotContainsString('Other Seller Product', $response->getContent());
    }

    private function sellerWithShop(): User
    {
        $seller = User::factory()->seller()->create();
        Shop::factory()->create(['user_id' => $seller->id]);

        return $seller;
    }
}

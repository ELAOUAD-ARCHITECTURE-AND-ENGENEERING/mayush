<?php

namespace Tests\Feature\V109;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class PromotionOffersTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
        $this->withoutMiddleware();
    }

    public function test_promotional_update_marks_checked_and_clears_unchecked_products(): void
    {
        $admin = User::factory()->admin()->create();
        $checked = Product::factory()->create(['promotional' => 0, 'todays_deal' => 0]);
        $unchecked = Product::factory()->create(['promotional' => 1, 'todays_deal' => 1]);

        $this->actingAs($admin)->post(route('promotional_products.update'), [
            'all_ids' => [$checked->id, $unchecked->id],
            'checked_ids' => [$checked->id],
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, (int) $checked->fresh()->promotional);
        $this->assertSame(0, (int) $unchecked->fresh()->promotional);
        $this->assertSame(0, (int) $unchecked->fresh()->todays_deal);
    }

    public function test_todays_deal_update_also_marks_products_promotional(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['promotional' => 0, 'todays_deal' => 0]);

        $this->actingAs($admin)->post(route('todays_deal_products.update'), [
            'all_ids' => [$product->id],
            'checked_ids' => [$product->id],
        ])->assertOk()->assertJson(['success' => true]);

        $product->refresh();
        $this->assertSame(1, (int) $product->todays_deal);
        $this->assertSame(1, (int) $product->promotional);
    }

    public function test_imported_promotion_routes_use_safe_methods(): void
    {
        $this->assertSame(['POST'], Route::getRoutes()->getByName('promotional_products.update')->methods());
        $this->assertSame(['POST'], Route::getRoutes()->getByName('todays_deal_products.update')->methods());
        $this->assertSame(['POST'], Route::getRoutes()->getByName('products.generate-with-ai')->methods());
    }
}

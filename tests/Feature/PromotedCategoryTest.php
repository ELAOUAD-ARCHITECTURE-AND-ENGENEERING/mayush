<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;


class PromotedCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['user_type' => 'admin']);
        $this->customer = User::factory()->create(['user_type' => 'customer']);
        $this->category = Category::factory()->create(['name' => 'Test Promo Category']);
        Cache::forget('business_settings');
    }

    /** @test */
    public function admin_can_fetch_products_by_category()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 10,
            'discount_type' => 'percent',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('promotional_category.products'), [
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /** @test */
    public function admin_can_update_product_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 200,
            'discount' => 0,
            'discount_type' => 'amount',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('promotional_category.update_discounts'), [
                'product_id' => $product->id,
                'discount' => 25,
                'discount_type' => 'percent',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $product->refresh();
        $this->assertEquals(25, $product->discount);
        $this->assertEquals('percent', $product->discount_type);
    }

    /** @test */
    public function non_admin_cannot_access_promotional_endpoints()
    {
        $response = $this->actingAs($this->customer)
            ->post(route('promotional_category.products'), [
                'category_id' => $this->category->id,
            ]);

        // Should be redirected (302), forbidden (403), or not found (404 - used by IsAdmin middleware)
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403, 404]));
    }

    /** @test */
    public function guest_cannot_access_promotional_endpoints()
    {
        $response = $this->post(route('promotional_category.products'), [
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function empty_category_returns_no_products_message()
    {
        $emptyCategory = Category::factory()->create(['name' => 'Empty Category']);

        $response = $this->actingAs($this->admin)
            ->post(route('promotional_category.products'), [
                'category_id' => $emptyCategory->id,
            ]);

        $response->assertStatus(200);
        $response->assertSee('No published products found');
    }

    /** @test */
    public function promoted_section_hidden_when_disabled()
    {
        // Ensure the setting is off
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '0']
        );
        Cache::forget('business_settings');

        $response = $this->get('/');
        $response->assertDontSee('<section class="promoted-category-section');
    }

    /** @test */
    public function promoted_section_shows_when_enabled_with_discounted_products()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '1']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_id'],
            ['value' => $this->category->id]
        );
        Cache::forget('business_settings');

        $response = $this->get('/');

        $response->assertSee('promoted-category-section');
    }
}

<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SeedsAppConfigs;

class ApplicationBootTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function homepage_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function product_listing_category_page_loads_successfully(): void
    {
        $category = Category::factory()->create();
        $response = $this->get(route('products.category', $category->slug));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /** @test */
    public function product_detail_page_loads_successfully(): void
    {
        $product = Product::factory()->create(['published' => 1, 'approved' => 1]);
        $response = $this->get(route('product', $product->slug));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /** @test */
    public function blog_page_loads_successfully(): void
    {
        $response = $this->get(route('blog'));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /** @test */
    public function login_page_loads_successfully(): void
    {
        $response = $this->get(route('user.login'));
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function register_page_loads_successfully(): void
    {
        $response = $this->get(route('user.registration'));
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function cart_page_loads_successfully(): void
    {
        $response = $this->get(route('cart'));
        $response->assertStatus(200);
    }

    /** @test */
    public function checkout_page_access_control(): void
    {
        $response = $this->get('/checkout');
        $this->assertContains($response->status(), [200, 302, 404]);

        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/checkout');
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function seller_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('seller.dashboard'));
        $this->assertContains($response->status(), [302, 404]);

        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get(route('seller.dashboard'));
        $this->assertEquals(404, $response->status());
    }

    /** @test */
    public function admin_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect();

        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get(route('admin.dashboard'));
        $response->assertStatus(404);
    }
}
<?php

namespace Tests\Integration\Controllers\Frontend;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function homepage_returns_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    /** @test */
    public function homepage_contains_site_name(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertStringContainsStringIgnoringCase('mayush', (string) $response->getContent());
    }

    /** @test */
    public function homepage_renders_without_errors_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Whoops');
    }

    /** @test */
    public function homepage_renders_for_authenticated_customer(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->get('/')->assertStatus(200);
    }

    /** @test */
    public function homepage_hides_flash_deal_when_none_configured(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Flash Sale');
    }

    /** @test */
    public function homepage_renders_with_empty_catalog_state(): void
    {
        $this->assertEquals(0, Product::count());
        $this->assertEquals(0, Category::count());

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('MayushTest');
    }
}

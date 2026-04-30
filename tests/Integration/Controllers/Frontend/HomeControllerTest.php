<?php

namespace Tests\Integration\Controllers\Frontend;

use Tests\TestCase;
use App\Models\User;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SeedsAppConfigs;

/**
 * HomeControllerTest
 *
 * Integration tests for the homepage route (GET /).
 * Seeds minimal BusinessSetting rows to make the view render correctly.
 */
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
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function homepage_contains_site_name(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        // Site name appears in meta/title or body
        $this->assertStringContainsStringIgnoringCase(
            'mayush',
            (string) $response->getContent()
        );
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
        $response = $this->actingAs($customer)->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function homepage_hides_flash_deal_when_none_configured(): void
    {
        // No FlashDeal seeded → flash-deal section should be absent
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Flash Sale');
    }

    /** @test */
    public function homepage_renders_correctly_with_empty_state(): void
    {
        // Ensure the database is clean (trait RefreshDatabase handles this, 
        // but we verify no products/categories were seeded)
        $this->assertEquals(0, \App\Models\Product::count());
        $this->assertEquals(0, \App\Models\Category::count());

        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Assert that the page still contains core structural elements 
        // even without dynamic content
        $response->assertSee('MayushTest'); // From BusinessSetting
    }
}

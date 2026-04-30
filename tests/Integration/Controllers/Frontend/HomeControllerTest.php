<?php

namespace Tests\Integration\Controllers\Frontend;

use Tests\TestCase;
use App\Models\User;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * HomeControllerTest
 *
 * Integration tests for the homepage route (GET /).
 * Seeds minimal BusinessSetting rows to make the view render correctly.
 */
class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );

        // Seed the minimum business settings required by the layout
        $settings = [
            'site_name'           => 'MayushTest',
            'language'            => 'en',
            'home_slider_images'  => null,
            'home_banner1_images' => null,
            'home_banner2_images' => null,
            'home_banner3_images' => null,
            'top10_categories'    => null,
            'top10_brands'        => null,
            'classified_product'  => '0',
            'google_login'        => '0',
            'facebook_login'      => '0',
            'twitter_login'       => '0',
            'apple_login'         => '0',
            'google_recaptcha'    => '0',
            'color_scheme'        => 'default',
            'frontend_logo'       => null,
        ];

        foreach ($settings as $key => $value) {
            BusinessSetting::updateOrCreate(['type' => $key], ['value' => $value]);
        }
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
}

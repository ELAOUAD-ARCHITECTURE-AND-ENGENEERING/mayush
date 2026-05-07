<?php

namespace Tests\Feature\Frontend;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Services\HomeLayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->setting('homepage_select', 'classic');
        $this->setting('vendor_system_activation', '0');
        $this->setting('best_selling', '0');
    }

    public function test_guest_can_view_homepage_with_empty_database(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-banner-area', false);
    }

    public function test_homepage_renders_with_minimal_seeded_category_settings(): void
    {
        $category = Category::factory()->create([
            'name' => 'Living Room',
            'slug' => 'living-room',
            'featured' => 1,
            'hot_category' => 1,
        ]);

        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Living Room');

        $data = app(HomeLayoutService::class)->getHomepageData();

        $this->assertTrue($data['featured_categories']->contains('id', $category->id));
        $this->assertTrue($data['hot_categories']->contains('id', $category->id));
        $this->assertSame('en', $data['lang']);
    }

    public function test_homepage_ajax_sections_accept_the_methods_used_by_frontend_scripts(): void
    {
        foreach ([
            'home.section.featured',
            'home.section.best_selling',
            'home.section.home_categories',
            'home.section.best_sellers',
            'home.section.auction_products',
            'load-elite-artisans-section',
        ] as $routeName) {
            $this->post(route($routeName))->assertOk();
        }

        foreach ([
            'home.section.todays_deal',
            'home.section.newest_products',
            'home.section.preorder_products',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    private function setting(string $type, string $value): void
    {
        BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
    }
}

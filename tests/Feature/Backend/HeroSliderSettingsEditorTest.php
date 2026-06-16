<?php

namespace Tests\Feature\Backend;

use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSliderSettingsEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_metro_hero_title_uses_rich_text_editor_markup(): void
    {
        $view = file_get_contents(resource_path('views/backend/website_settings/pages/metro/home_page_edit.blade.php'));

        $this->assertStringContainsString('name="home_slider_titles[]"', $view);
        $this->assertStringContainsString('aiz-text-editor hero-title-editor', $view);
        $this->assertStringContainsString('"color", ["color"]', $view);
        $this->assertStringContainsString('initHeroTitleEditors', $view);
        $this->assertStringContainsString('syncHeroTitleEditors', $view);
    }

    public function test_metro_home_categories_tab_has_visibility_toggle(): void
    {
        $view = file_get_contents(resource_path('views/backend/website_settings/pages/metro/home_page_edit.blade.php'));

        $this->assertStringContainsString('name="home_categories_section_status"', $view);
        $this->assertStringContainsString('Show Category Wise Products', $view);
        $this->assertStringContainsString('aiz-switch aiz-switch-success', $view);
    }

    public function test_metro_home_setup_has_inspiration_articles_controls(): void
    {
        $view = file_get_contents(resource_path('views/backend/website_settings/pages/metro/home_page_edit.blade.php'));

        $this->assertStringContainsString('id="inspiration-articles-tab"', $view);
        $this->assertStringContainsString('name="home_inspiration_section_status"', $view);
        $this->assertStringContainsString('name="home_inspiration_blog_ids[]"', $view);
        $this->assertStringContainsString('data-max-options="6"', $view);
        $this->assertStringContainsString('Leave empty to automatically show the latest 6 published blog articles.', $view);
    }

    public function test_metro_collections_split_editor_has_copy_and_cta_controls(): void
    {
        $view = file_get_contents(resource_path('views/backend/website_settings/pages/metro/home_page_edit.blade.php'));

        $this->assertStringContainsString('$metroCollectionPanels', $view);
        $this->assertStringContainsString('name="metro_collections_{{ $panelKey }}_title"', $view);
        $this->assertStringContainsString('name="metro_collections_{{ $panelKey }}_description"', $view);
        $this->assertStringContainsString('name="metro_collections_{{ $panelKey }}_cta_text"', $view);
        $this->assertStringContainsString('name="metro_collections_{{ $panelKey }}_cta_link"', $view);
        $this->assertStringContainsString('CTA Button Text', $view);
        $this->assertStringContainsString('This copy appears over the panel image on the homepage.', $view);
    }

    public function test_metro_collections_split_copy_and_cta_settings_are_saved(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('business_settings.update'), [
            'tab' => 'collections_split',
            'types' => [
                'metro_collections_section_status',
                ['en' => 'metro_collections_newest_title'],
                ['en' => 'metro_collections_newest_description'],
                ['en' => 'metro_collections_newest_cta_text'],
                ['en' => 'metro_collections_newest_cta_link'],
                ['en' => 'metro_collections_best_selling_title'],
                ['en' => 'metro_collections_best_selling_description'],
                ['en' => 'metro_collections_best_selling_cta_text'],
                ['en' => 'metro_collections_best_selling_cta_link'],
            ],
            'metro_collections_section_status' => '1',
            'metro_collections_newest_title' => 'Fresh Metro Collections',
            'metro_collections_newest_description' => 'Curated new furniture and decor.',
            'metro_collections_newest_cta_text' => 'Explore New',
            'metro_collections_newest_cta_link' => '/search?sort_by=newest',
            'metro_collections_best_selling_title' => 'Best Sellers for Home',
            'metro_collections_best_selling_description' => 'Customer favorites for the season.',
            'metro_collections_best_selling_cta_text' => 'Shop Best Sellers',
            'metro_collections_best_selling_cta_link' => '/search?sort_by=popular',
        ])->assertRedirect();

        $this->assertDatabaseHas('business_settings', [
            'type' => 'metro_collections_newest_title',
            'lang' => 'en',
            'value' => 'Fresh Metro Collections',
        ]);
        $this->assertDatabaseHas('business_settings', [
            'type' => 'metro_collections_newest_description',
            'lang' => 'en',
            'value' => 'Curated new furniture and decor.',
        ]);
        $this->assertDatabaseHas('business_settings', [
            'type' => 'metro_collections_newest_cta_text',
            'lang' => 'en',
            'value' => 'Explore New',
        ]);
        $this->assertDatabaseHas('business_settings', [
            'type' => 'metro_collections_best_selling_cta_link',
            'lang' => 'en',
            'value' => '/search?sort_by=popular',
        ]);
    }

    public function test_hero_title_rich_text_html_is_saved_safely(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('business_settings.update'), [
            'tab' => 'home_slider',
            'types' => [
                ['en' => 'home_slider_titles'],
            ],
            'home_slider_titles' => [
                'Design <font color="#d97434"><b>Premium</b></font><span style="font-weight: bold; font-style: italic; text-decoration: underline;">Home</span><script>alert("x")</script>',
            ],
        ])->assertRedirect();

        $setting = BusinessSetting::where('type', 'home_slider_titles')->where('lang', 'en')->firstOrFail();

        $this->assertSame(
            ['Design <span style="color: #d97434;"><b>Premium</b></span><span style="font-weight: 700; font-style: italic; text-decoration: underline;">Home</span>'],
            json_decode($setting->value, true)
        );
    }
}

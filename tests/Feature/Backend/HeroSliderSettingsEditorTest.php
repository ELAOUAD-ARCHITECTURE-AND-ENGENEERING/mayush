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

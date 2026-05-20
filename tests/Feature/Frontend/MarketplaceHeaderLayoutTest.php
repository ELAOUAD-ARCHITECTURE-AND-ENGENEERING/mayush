<?php

namespace Tests\Feature\Frontend;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\ElementType;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketplaceHeaderLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_mayush_marketplace_header_layout_is_registered_and_rendered(): void
    {
        $header = ElementType::where('name', 'Header 7')->firstOrFail();

        Language::factory()->create([
            'name' => 'English',
            'code' => 'en',
            'app_lang_code' => 'en',
            'status' => 1,
        ]);

        DB::table('currencies')->insert([
            'id' => 1,
            'name' => 'Moroccan Dirham',
            'symbol' => 'Dhs',
            'exchange_rate' => 1,
            'code' => 'MAD',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Category::factory()->create([
            'name' => 'Office Furniture',
            'slug' => 'office-furniture',
            'level' => 0,
        ]);

        $this->setting('header_element', (string) $header->id);
        $this->setting('homepage_select', 'metro');
        $this->setting('show_language_switcher', 'on');
        $this->setting('show_currency_switcher', 'on');
        $this->setting('system_default_currency', '1');
        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('mayush-market-header', false)
            ->assertSee('Search Mayush Design')
            ->assertSee('Deliver to')
            ->assertSee('Morocco')
            ->assertSee('Account &amp; Lists', false)
            ->assertSee('Office Furniture')
            ->assertSee('id="lang-change"', false);
    }

    public function test_admin_can_select_marketplace_header_layout(): void
    {
        $admin = User::factory()->admin()->create();
        $header = ElementType::where('name', 'Header 7')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('settings.select-header'), [
                'header_element' => $header->id,
            ])
            ->assertRedirect();

        $this->assertSame((string) $header->id, get_setting('header_element'));
        $this->assertSame('#111827', get_setting('top_header_bg_color'));
        $this->assertSame('#243244', get_setting('bottom_header_bg_color'));
    }

    public function test_header_selector_uses_database_ids_and_has_generated_preview_fallback(): void
    {
        $view = file_get_contents(resource_path('views/backend/website_settings/select_header.blade.php'));

        $this->assertStringContainsString('value="{{ $element_type->id }}"', $view);
        $this->assertStringContainsString('header-layout-preview-marketplace', $view);
        $this->assertStringContainsString("route('settings.select-header')", $view);
    }

    private function setting(string $type, string $value): void
    {
        BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
    }
}

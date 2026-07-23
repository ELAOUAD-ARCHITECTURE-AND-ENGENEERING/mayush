<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class MetroArabicHomepageTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_todays_deal_section_renders_arabic_content_when_arabic_session_is_active(): void
    {
        \App\Models\BusinessSetting::updateOrCreate(['type' => 'homepage_select'], ['value' => 'metro']);
        Product::factory()->create(['todays_deal' => 1, 'published' => 1]);

        $response = $this->withSession(['locale' => 'ma'])
            ->get(route('home.section.todays_deal'));

        $response->assertOk()
            ->assertSee('القطع الأكثر طلباً')
            ->assertSee('عرض اليوم المحدود')
            ->assertSee('أيام')
            ->assertSee('ساعات')
            ->assertSee('دقائق')
            ->assertSee('ثواني');
    }

    public function test_homepage_renders_successfully_with_arabic_locale(): void
    {
        $response = $this->withSession(['locale' => 'ma'])
            ->get(route('home'));

        $response->assertOk();
    }
}

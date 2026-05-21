<?php

namespace Tests\Feature\Backend;

use App\Models\BannerTextVersion;
use App\Models\BusinessSetting;
use App\Models\Upload;
use App\Models\User;
use App\Services\BannerTextSanitizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PromoBannerRichTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_sanitizer_keeps_controlled_styles_and_removes_xss_vectors(): void
    {
        $html = '<p style="color: #AABBCC; background-color: rgb(1, 2, 3); font-family: Inter, sans-serif; font-size: 2rem; text-align: center; line-height: 1.4; letter-spacing: .05em; position: fixed;" onclick="alert(1)">'
            . '<strong>Safe</strong><img src=x onerror=alert(1)><span style="font-family: Papyrus; font-size: calc(1rem + 2vw)">Text</span>'
            . '</p><script>alert(2)</script>';

        $sanitized = app(BannerTextSanitizerService::class)->sanitize($html);

        $this->assertStringContainsString('<p style="color: #aabbcc; background-color: rgb(1, 2, 3); font-family: Inter, sans-serif; font-size: 2rem; text-align: center; line-height: 1.4; letter-spacing: .05em">', $sanitized);
        $this->assertStringContainsString('<strong>Safe</strong>', $sanitized);
        $this->assertStringContainsString('<span>Text</span>', $sanitized);
        $this->assertStringNotContainsString('onclick', $sanitized);
        $this->assertStringNotContainsString('onerror', $sanitized);
        $this->assertStringNotContainsString('position', $sanitized);
        $this->assertStringNotContainsString('script', $sanitized);
        $this->assertStringNotContainsString('<img', $sanitized);
    }

    public function test_business_settings_sanitize_banner_copy_and_snapshot_previous_value(): void
    {
        $admin = User::factory()->admin()->create();

        BusinessSetting::create([
            'type' => 'home_banner1_titles',
            'lang' => 'en',
            'value' => json_encode(['Old title']),
        ]);

        $this->actingAs($admin)->post(route('business_settings.update'), [
            'types' => [
                ['en' => 'home_banner1_titles'],
                ['en' => 'home_banner1_descriptions'],
                ['en' => 'home_banner1_cta_texts'],
            ],
            'home_banner1_titles' => [
                '<span style="color: #123456; font-family: Playfair Display; font-size: 32px">New <b>Title</b></span><script>bad()</script>',
            ],
            'home_banner1_descriptions' => [
                '<div style="text-align: right; letter-spacing: 0.1em">Detail</div>',
            ],
            'home_banner1_cta_texts' => [
                '<b>Shop</b>',
            ],
        ])->assertRedirect();

        $title = BusinessSetting::where('type', 'home_banner1_titles')->where('lang', 'en')->firstOrFail();
        $description = BusinessSetting::where('type', 'home_banner1_descriptions')->where('lang', 'en')->firstOrFail();
        $cta = BusinessSetting::where('type', 'home_banner1_cta_texts')->where('lang', 'en')->firstOrFail();
        $version = BannerTextVersion::where('setting_key', 'home_banner1_titles')->firstOrFail();

        $this->assertSame(
            ['<span style="color: #123456; font-family: \'Playfair Display\'; font-size: 32px">New <b>Title</b></span>'],
            json_decode($title->value, true)
        );
        $this->assertSame(
            ['<div style="text-align: right; letter-spacing: 0.1em">Detail</div>'],
            json_decode($description->value, true)
        );
        $this->assertSame(['<b>Shop</b>'], json_decode($cta->value, true));
        $this->assertSame(json_encode(['Old title']), $version->value);
        $this->assertSame($admin->id, $version->changed_by);
    }

    public function test_banner_history_restore_is_admin_protected_resanitized_and_retained(): void
    {
        $admin = User::factory()->admin()->create();
        $setting = BusinessSetting::create([
            'type' => 'home_banner2_titles',
            'lang' => 'en',
            'value' => json_encode(['Current']),
        ]);

        $version = BannerTextVersion::create([
            'setting_key' => 'home_banner2_titles',
            'lang' => 'en',
            'value' => json_encode(['<span style="color: #abcdef">Restored</span><script>x()</script>']),
            'changed_by' => $admin->id,
        ]);

        $this->get(route('banner_versions.index', ['settingKey' => 'home_banner2_titles', 'lang' => 'en']))
            ->assertRedirect();

        Cache::put('business_settings', BusinessSetting::all(), 86400);

        $this->actingAs($admin)
            ->get(route('banner_versions.index', ['settingKey' => 'home_banner2_titles', 'lang' => 'en']))
            ->assertOk()
            ->assertJsonPath('versions.0.id', $version->id);

        $this->actingAs($admin)
            ->post(route('banner_versions.restore', $version))
            ->assertOk();

        $setting->refresh();
        $this->assertSame(
            ['<span style="color: #abcdef">Restored</span>'],
            json_decode($setting->value, true)
        );
        $this->assertSame(json_encode(['Current']), BannerTextVersion::latest('id')->firstOrFail()->value);
        $this->assertFalse(Cache::has('business_settings'));

        $versionService = app(\App\Services\BannerTextVersionService::class);
        foreach (range(1, 22) as $index) {
            $versionService->snapshot('home_banner2_descriptions', 'en', json_encode(['Version ' . $index]), $admin->id);
        }

        $this->assertSame(20, BannerTextVersion::where('setting_key', 'home_banner2_descriptions')->where('lang', 'en')->count());
    }

    public function test_metro_promo_banner_renders_sanitized_html_and_escapes_cta(): void
    {
        $upload = Upload::create([
            'file_original_name' => 'promo.jpg',
            'file_name' => 'uploads/test-promo.jpg',
            'extension' => 'jpg',
            'type' => 'image',
            'file_size' => 1024,
        ]);

        $this->setting('homepage_select', 'metro');
        $this->setting('home_banner1_images', json_encode([$upload->id]));
        $this->setting('home_banner1_links', json_encode(['https://example.test/promo']));
        $this->setting('home_banner1_titles', json_encode(['<strong>Styled title</strong><script>bad()</script>']));
        $this->setting('home_banner1_descriptions', json_encode(['<span style="color: #ffffff">Styled detail</span>']));
        $this->setting('home_banner1_cta_texts', json_encode(['<b>Buy</b>']));
        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<strong>Styled title</strong>', false)
            ->assertDontSee('bad()', false)
            ->assertSee('<span style="color: #ffffff">Styled detail</span>', false)
            ->assertSee('&lt;b&gt;Buy&lt;/b&gt;', false)
            ->assertDontSee('<b>Buy</b>', false);
    }

    private function setting(string $type, string $value): void
    {
        BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
    }
}

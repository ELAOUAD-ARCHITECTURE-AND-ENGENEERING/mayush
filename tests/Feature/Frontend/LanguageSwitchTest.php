<?php

namespace Tests\Feature\Frontend;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_language_switch_updates_session_and_returns_json(): void
    {
        $language = Language::factory()->create([
            'name' => 'Français',
            'code' => 'fr',
            'app_lang_code' => 'fr',
            'status' => 1,
        ]);

        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson(route('language.change'), ['locale' => $language->code]);

        $response->assertOk()
            ->assertJson(['success' => true, 'locale' => 'fr']);
        $this->assertSame('fr', session('locale'));
        $this->assertSame('fr', session('langcode'));
    }

    public function test_ajax_language_switch_rejects_unknown_or_inactive_locale(): void
    {
        Language::factory()->create([
            'code' => 'fr',
            'app_lang_code' => 'fr',
            'status' => 0,
        ]);

        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson(route('language.change'), ['locale' => 'fr']);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);
        $this->assertSame('en', session('locale'));
    }

    public function test_ajax_language_switch_remains_available_during_maintenance(): void
    {
        $language = Language::factory()->create([
            'code' => 'fr',
            'app_lang_code' => 'fr',
            'status' => 1,
        ]);

        config(['app.maintenance.driver' => 'cache']);
        app()->maintenanceMode()->activate([]);

        try {
            $response = $this->postJson(route('language.change'), ['locale' => $language->code]);

            $response->assertOk()
                ->assertJson(['success' => true, 'locale' => 'fr']);
        } finally {
            app()->maintenanceMode()->deactivate();
        }
    }
}

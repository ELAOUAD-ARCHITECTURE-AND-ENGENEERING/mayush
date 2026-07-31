<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SupportBotDeploymentReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_bot_catalog_is_bootstrapped_by_migrations(): void
    {
        $this->assertGreaterThan(0, DB::table('support_categories')->count());
        $this->assertGreaterThan(0, DB::table('support_cases')->count());
        $this->assertDatabaseHas('support_categories', [
            'code' => 'OR',
            'name' => 'Orders',
        ]);
        $this->assertDatabaseHas('support_cases', [
            'case_code' => 'OR-002',
        ]);
    }

    public function test_live_chat_uses_web_root_avatar_urls(): void
    {
        $rendered = view('frontend.inc.live_chat_widget')->render();
        $botAvatar = asset('assets/img/mayush-bot-avatar.png');
        $legacyBotAvatar = static_asset('assets/img/mayush-bot-avatar.png');

        $this->assertStringContainsString($botAvatar, $rendered);
        $this->assertStringNotContainsString($legacyBotAvatar, $rendered);
    }
}

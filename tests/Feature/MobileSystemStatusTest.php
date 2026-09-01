<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Tests\TestCase;

class MobileSystemStatusTest extends TestCase
{
    public function test_it_returns_no_update_for_current_binary(): void
    {
        config()->set('mobile_system.update.latest_version', '1.2.0');
        config()->set('mobile_system.update.minimum_version', '1.0.0');

        $this->getJson('/api/v2/system/status?platform=android&app_version=1.2.0&runtime_version=1.2.0&locale=fr')
            ->assertOk()
            ->assertJsonPath('data.update.status', 'none')
            ->assertJsonPath('data.maintenance.status', 'none');
    }

    public function test_it_forces_store_update_below_minimum_version(): void
    {
        config()->set('mobile_system.update.latest_version', '2.0.0');
        config()->set('mobile_system.update.minimum_version', '1.5.0');
        config()->set('mobile_system.update.store_urls.android', 'https://example.test/android');

        $this->getJson('/api/v2/system/status?platform=android&app_version=1.0.0&runtime_version=1.0.0&locale=fr')
            ->assertOk()
            ->assertJsonPath('data.update.status', 'mandatory')
            ->assertJsonPath('data.update.delivery', 'store')
            ->assertJsonPath('data.update.store_url', 'https://example.test/android');
    }

    public function test_it_offers_ota_only_for_matching_runtime(): void
    {
        config()->set('mobile_system.update.latest_version', '1.1.0');
        config()->set('mobile_system.update.minimum_version', '1.0.0');
        config()->set('mobile_system.update.latest_runtime_version', '1.0.0');

        $this->getJson('/api/v2/system/status?platform=ios&app_version=1.0.0&runtime_version=1.0.0&locale=fr')
            ->assertOk()
            ->assertJsonPath('data.update.status', 'optional')
            ->assertJsonPath('data.update.delivery', 'ota');
    }

    public function test_active_maintenance_is_a_global_block(): void
    {
        config()->set('mobile_system.maintenance.active', true);

        $this->getJson('/api/v2/system/status?platform=web&app_version=1.0.0&locale=ar')
            ->assertOk()
            ->assertJsonPath('data.maintenance.status', 'active')
            ->assertJsonPath('data.maintenance.global_block', true)
            ->assertJsonPath('data.maintenance.title', config('mobile_system.maintenance.title.ar'));
    }

    public function test_scheduled_maintenance_is_localized_and_non_blocking(): void
    {
        CarbonImmutable::setTestNow('2026-08-15T12:00:00Z');
        config()->set('mobile_system.maintenance.starts_at', '2026-08-15T14:00:00Z');
        config()->set('mobile_system.maintenance.title.ar', 'صيانة مجدولة');

        $this->getJson('/api/v2/system/status?platform=android&app_version=1.0.0&locale=ar')
            ->assertOk()
            ->assertJsonPath('data.maintenance.status', 'scheduled')
            ->assertJsonPath('data.maintenance.global_block', false)
            ->assertJsonPath('data.maintenance.title', 'صيانة مجدولة');

        CarbonImmutable::setTestNow();
    }

    public function test_mandatory_update_never_bypasses_gate_when_store_url_is_missing(): void
    {
        config()->set('mobile_system.update.latest_version', '2.0.0');
        config()->set('mobile_system.update.minimum_version', '2.0.0');
        config()->set('mobile_system.update.store_urls.ios', null);

        $this->getJson('/api/v2/system/status?platform=ios&app_version=1.0.0&runtime_version=1.0.0&locale=fr')
            ->assertOk()
            ->assertJsonPath('data.update.status', 'mandatory')
            ->assertJsonPath('data.update.delivery', 'store')
            ->assertJsonPath('data.update.store_url', null);
    }
}

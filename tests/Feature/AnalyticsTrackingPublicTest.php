<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stevebauman\Location\Facades\Location;
use Tests\TestCase;

class AnalyticsTrackingPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_visit_tracking_posts_without_csrf_or_system_key(): void
    {
        Location::shouldReceive('get')->andReturn(false);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/mayush/',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->postJson('/api/v2/analytics/track-visit', [
            'session_id' => 'browser-session-1',
            'url' => '/mayush/',
            'method' => 'GET',
            'is_entry' => true,
        ])->assertOk()->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('visitor_metrics', [
            'session_id' => 'browser-session-1',
            'url' => '/mayush/',
        ]);
    }

    public function test_browser_health_tracking_posts_without_csrf_or_system_key(): void
    {
        $this->withHeaders([
            'Accept' => 'application/json',
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/mayush/',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->postJson('/api/v2/analytics/track-health', [
            'type' => 'latency',
            'source' => 'frontend',
            'value' => 42,
            'unit' => 'ms',
            'message' => 'Page load smoke',
        ])->assertOk()->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('health_metrics', [
            'type' => 'latency',
            'source' => 'frontend',
            'unit' => 'ms',
        ]);
    }
}

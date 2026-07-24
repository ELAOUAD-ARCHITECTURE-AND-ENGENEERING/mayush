<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;

class BuyerDashboardNotificationSurfaceTest extends TestCase
{
    public function test_buyer_dashboard_uses_the_shared_notification_center_trigger_before_the_profile_chip(): void
    {
        $layout = file_get_contents(resource_path('views/frontend/layouts/user_panel.blade.php'));

        $trigger = "@include('partials.notification-center-trigger', ['variant' => 'buyer'])";
        $profileChip = "{{-- User chip --}}";

        $this->assertStringContainsString($trigger, $layout);
        $this->assertLessThan(
            strpos($layout, $profileChip),
            strpos($layout, $trigger),
            'The notification trigger must render before the buyer profile chip.'
        );
        $this->assertSame(1, substr_count($layout, $trigger));
        $this->assertStringNotContainsString('unreadNotifications', $layout);
    }
}

<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;

class SellerNotificationSurfaceTest extends TestCase
{
    public function test_seller_navigation_uses_the_shared_notification_center_trigger(): void
    {
        $navigation = file_get_contents(resource_path('views/seller/inc/seller_nav.blade.php'));

        $this->assertStringContainsString(
            "@include('partials.notification-center-trigger', ['variant' => 'seller'])",
            $navigation
        );
        $this->assertStringNotContainsString('unreadNotifications', $navigation);
        $this->assertStringNotContainsString('<i class="las la-bell fs-24"></i>', $navigation);
    }
}

<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;

class NotificationCenterUiRuntimeTest extends TestCase
{
    public function test_dashboard_navigation_preserves_legacy_notifications_until_v2_is_enabled(): void
    {
        $sellerNavigation = file_get_contents(resource_path('views/seller/inc/seller_nav.blade.php'));
        $adminNavigation = file_get_contents(resource_path('views/backend/inc/admin_nav.blade.php'));

        foreach ([$sellerNavigation, $adminNavigation] as $navigation) {
            $this->assertStringContainsString("config('notifications_v2.enabled')", $navigation);
            $this->assertStringContainsString("partials.notification-center-trigger", $navigation);
            $this->assertStringContainsString("partials.legacy-notification-trigger", $navigation);
        }
    }

    public function test_v2_runtime_bridges_server_action_flash_messages_to_custom_toasts(): void
    {
        $runtime = file_get_contents(resource_path('views/partials/notification-center-runtime.blade.php'));
        $storefrontLayout = file_get_contents(resource_path('views/frontend/layouts/app.blade.php'));

        $this->assertStringContainsString("session('flash_notification'", $runtime);
        $this->assertStringContainsString("new CustomEvent('mayush:toast'", $runtime);
        $this->assertStringContainsString("!config('notifications_v2.enabled') || !auth()->check()", $storefrontLayout);
    }

    public function test_client_runtime_only_loads_reverb_dependencies_when_broadcasting_is_configured(): void
    {
        $runtime = file_get_contents(resource_path('js/storefront/notifications.js'));

        $this->assertStringContainsString("runtime.dataset.broadcasting !== '1'", $runtime);
        $this->assertStringContainsString("import('laravel-echo')", $runtime);
        $this->assertStringContainsString("import('pusher-js')", $runtime);
        $this->assertStringContainsString("textContent =", $runtime);
    }

    public function test_admin_trigger_uses_the_light_mayush_topbar_theme_and_alignment(): void
    {
        $trigger = file_get_contents(resource_path('views/partials/notification-center-trigger.blade.php'));

        $this->assertStringContainsString('.aiz-topbar .mayush-notification-center--admin', $trigger);
        $this->assertStringContainsString('color: var(--notification-muted);', $trigger);
        $this->assertStringContainsString('var(--mayush-orange, #D97434)', $trigger);
        $this->assertStringContainsString('var(--mayush-beige, #F5F1E8)', $trigger);
        $this->assertStringNotContainsString('.mayush-notification-center--admin,', $trigger);
    }

    public function test_dropdown_uses_grouped_rows_with_a_mark_all_read_action(): void
    {
        $trigger = file_get_contents(resource_path('views/partials/notification-center-trigger.blade.php'));
        $runtime = file_get_contents(resource_path('js/storefront/notifications.js'));

        $this->assertStringContainsString('data-notification-read-all', $trigger);
        $this->assertStringContainsString('data-notification-groups', $trigger);
        $this->assertStringContainsString('mayush-notification-group__label', $trigger);
        $this->assertStringContainsString('notificationGroup', $runtime);
        $this->assertStringContainsString('notificationIconPath', $runtime);
        $this->assertStringContainsString('notificationEventIconKey', $runtime);
        $this->assertStringContainsString("'order.delivered': 'order-delivered'", $runtime);
        $this->assertStringContainsString("'payment.failed': 'payment-failed'", $runtime);
        $this->assertStringContainsString('createNotificationSignal', $runtime);
        $this->assertStringContainsString('notificationSeverity', $runtime);
        $this->assertStringContainsString("key.startsWith('order.')", $runtime);
        $this->assertStringContainsString('notificationCategory(notification?.category, notification?.event_key)', $runtime);
        $this->assertStringContainsString('mayush-notification-dropdown__signal--${type}-${value}', $runtime);
        $this->assertStringContainsString('groups.replaceChildren', $runtime);
    }

    public function test_storefront_triggers_use_direct_propagation_safe_click_handlers(): void
    {
        $runtime = file_get_contents(resource_path('js/storefront/notifications.js'));

        $this->assertStringContainsString("document.querySelectorAll('[data-notification-trigger]').forEach", $runtime);
        $this->assertStringContainsString('event.stopPropagation();', $runtime);
        $this->assertStringContainsString('toggleDropdown(trigger);', $runtime);
    }
}

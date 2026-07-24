<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;

class NotificationInboxUiTest extends TestCase
{
    public function test_every_role_uses_the_shared_notification_inbox_shell(): void
    {
        $admin = file_get_contents(resource_path('views/backend/notification/index.blade.php'));
        $seller = file_get_contents(resource_path('views/seller/notification/index.blade.php'));
        $buyer = file_get_contents(resource_path('views/frontend/user/customer/notification/index.blade.php'));

        $this->assertStringContainsString("'notificationInboxVariant' => 'admin'", $admin);
        $this->assertStringContainsString("'notificationInboxVariant' => 'seller'", $seller);
        $this->assertStringContainsString("'notificationInboxVariant' => 'buyer'", $buyer);

        foreach ([$admin, $seller, $buyer] as $view) {
            $this->assertStringContainsString("@include('partials.notification-inbox'", $view);
            $this->assertStringContainsString("'notificationUnreadCount'", $view);
        }
    }

    public function test_shared_inbox_exposes_accessible_filters_selection_and_bulk_archive_controls(): void
    {
        $inbox = file_get_contents(resource_path('views/partials/notification-inbox.blade.php'));
        $filters = file_get_contents(resource_path('views/partials/notification-filters.blade.php'));
        $script = file_get_contents(resource_path('views/partials/notification-inbox-script.blade.php'));

        $this->assertStringContainsString('data-notification-inbox', $inbox);
        $this->assertStringContainsString('data-notification-select-all', $inbox);
        $this->assertStringContainsString('data-notification-bulk-action', $inbox);
        $this->assertStringContainsString('aria-label="{{ translate(\'Filter notifications\') }}"', $filters);
        $this->assertStringContainsString("bulkAction.addEventListener('click'", $script);
        $this->assertStringContainsString('notification_ids[]', $script);
    }

    public function test_rows_use_contextual_svg_icons_and_legacy_event_category_fallbacks(): void
    {
        $component = file_get_contents(resource_path('views/components/notification.blade.php'));

        $this->assertStringContainsString('$legacyEventCategories', $component);
        $this->assertStringContainsString('mayush-notification-item__icon--{{ $categoryKey }}', $component);
        $this->assertStringContainsString('mayush-notification-item__icon--event-{{ $eventIconKey }}', $component);
        $this->assertStringContainsString("'order-delivered' =>", $component);
        $this->assertStringContainsString("'payment-failed' =>", $component);
        $this->assertStringContainsString('mayush-notification-item__signal--priority-{{ $severity }}', $component);
        $this->assertStringContainsString('mayush-notification-item__signal--status-{{ $statusKey }}', $component);
        $this->assertStringContainsString('mayush-notification-item--unread', $component);
        $this->assertStringContainsString('data-notification-toggle-read', $component);
    }

    public function test_full_inbox_row_styles_are_scoped_away_from_the_compact_dropdown(): void
    {
        $inbox = file_get_contents(resource_path('views/partials/notification-inbox.blade.php'));

        $this->assertStringContainsString('.mayush-notification-inbox .mayush-notification-item {', $inbox);
        $this->assertStringContainsString('.mayush-notification-inbox .mayush-notification-list {', $inbox);
        $this->assertStringNotContainsString("\n        .mayush-notification-item {", $inbox);
        $this->assertStringNotContainsString("\n        .mayush-notification-list {", $inbox);
    }

    public function test_shared_inbox_uses_the_canonical_mayush_palette_tokens(): void
    {
        $inbox = file_get_contents(resource_path('views/partials/notification-inbox.blade.php'));

        $this->assertStringContainsString('var(--mayush-orange, #D97434)', $inbox);
        $this->assertStringContainsString('var(--mayush-beige, #F5F1E8)', $inbox);
        $this->assertStringContainsString('var(--mayush-border, #E5E0D8)', $inbox);
        $this->assertStringContainsString('var(--mayush-success, #00A86B)', $inbox);
        $this->assertStringContainsString('var(--mayush-danger, #E53935)', $inbox);
        $this->assertStringNotContainsString('#377dff', strtolower($inbox));
        $this->assertStringNotContainsString('#0f9d91', strtolower($inbox));
    }
}

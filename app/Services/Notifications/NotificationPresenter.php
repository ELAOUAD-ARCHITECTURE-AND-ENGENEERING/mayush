<?php

namespace App\Services\Notifications;

use App\Models\NotificationType;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationPresenter
{
    public function present(DatabaseNotification $notification, int $unreadCount = 0): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        if (($data['schema_version'] ?? null) === 1) {
            return array_merge($data, [
                'id' => $notification->id,
                'read_at' => $this->iso($notification->read_at),
                'created_at' => $this->iso($notification->created_at),
                'unread_count' => $unreadCount,
                'archived_at' => $this->iso($notification->archived_at),
            ]);
        }

        $type = !empty($notification->notification_type_id)
            ? NotificationType::find($notification->notification_type_id)
            : null;
        $title = $type?->getTranslation('name')
            ?: Str::headline(class_basename($notification->type ?: 'Notification'));
        $message = $type?->getTranslation('default_text') ?: $title;

        if (!empty($data['order_code'])) {
            $message = str_replace('[[order_code]]', (string) $data['order_code'], $message);
        }

        return [
            'schema_version' => 1,
            'id' => $notification->id,
            'event_key' => $this->legacyEventKey($notification->type),
            'category' => $notification->category ?: $type?->category ?: 'system',
            'severity' => $notification->severity ?: $type?->severity ?: 'info',
            'title' => $title,
            'message' => $message,
            'action_url' => null,
            'mandatory_inbox' => (bool) ($type?->mandatory_inbox ?? false),
            'read_at' => $this->iso($notification->read_at),
            'created_at' => $this->iso($notification->created_at),
            'unread_count' => $unreadCount,
            'archived_at' => $this->iso($notification->archived_at),
            'legacy' => true,
            'legacy_type' => $notification->type,
            'legacy_data' => $data,
        ];
    }

    private function legacyEventKey(?string $className): string
    {
        return match (strtolower((string) $className)) {
            'app\\notifications\\ordernotification' => 'order.placed',
            'app\\notifications\\payoutnotification' => 'payout.status',
            'app\\notifications\\shopverificationnotification' => 'seller.status',
            'app\\notifications\\shopproductnotification' => 'product.status',
            'app\\notifications\\productrestockednotification' => 'product.restocked',
            'app\\notifications\\predictiverestocknotification' => 'stock.alert',
            'app\\notifications\\customnotification' => 'custom.sent',
            default => 'legacy.notification',
        };
    }

    private function iso($value): ?string
    {
        return $value ? Carbon::parse($value)->toIso8601String() : null;
    }
}

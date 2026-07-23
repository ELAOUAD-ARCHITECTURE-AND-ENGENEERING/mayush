<?php

namespace App\Services\Notifications;

use App\Models\NotificationEvent;
use App\Models\User;
use Illuminate\Support\Arr;

class NotificationPayload
{
    private const SAFE_EVENT_KEYS = [
        'title',
        'message',
        'action_url',
        'order_id',
        'order_code',
        'status',
        'payment_id',
        'refund_id',
        'dispute_id',
        'payout_id',
        'seller_id',
        'product_id',
        'message_id',
        'campaign_id',
        'locale',
    ];

    public function safeEventPayload(array $payload): array
    {
        $safe = Arr::only($payload, self::SAFE_EVENT_KEYS);

        foreach ($safe as $key => $value) {
            if (!is_null($value) && !is_scalar($value)) {
                unset($safe[$key]);
                continue;
            }

            if (is_string($value)) {
                $safe[$key] = mb_substr(strip_tags($value), 0, $key === 'message' ? 1000 : 255);
            }
        }

        $safe['action_url'] = $this->safeActionUrl($safe['action_url'] ?? null);

        return $safe;
    }

    public function inbox(
        NotificationEvent $event,
        User $user,
        string $notificationId,
        int $unreadCount = 0
    ): array {
        $definition = app(NotificationCatalog::class)->get($event->event_key);
        $eventPayload = $event->payload ?: [];

        return [
            'schema_version' => 1,
            'id' => $notificationId,
            'event_key' => $event->event_key,
            'category' => $event->category,
            'severity' => $event->severity,
            'title' => $eventPayload['title'] ?? $definition['title'],
            'message' => $eventPayload['message'] ?? $definition['title'],
            'action_url' => $this->resolveActionUrl($user, $eventPayload),
            'mandatory_inbox' => (bool) $definition['mandatory_inbox'],
            'read_at' => null,
            'created_at' => now()->toIso8601String(),
            'unread_count' => $unreadCount,
        ];
    }

    public function safeActionUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        $parts = parse_url($url);
        $app = parse_url((string) config('app.url'));
        if (($parts['scheme'] ?? null) !== 'https'
            || empty($parts['host'])
            || strcasecmp($parts['host'], $app['host'] ?? '') !== 0) {
            return null;
        }

        return $url;
    }

    private function resolveActionUrl(User $user, array $payload): ?string
    {
        if (!empty($payload['order_id'])) {
            try {
                return match ($user->user_type) {
                    'admin', 'staff' => route('all_orders.show', encrypt($payload['order_id']), false),
                    'seller' => route('seller.orders.show', encrypt($payload['order_id']), false),
                    default => route('purchase_history.details', encrypt($payload['order_id']), false),
                };
            } catch (\Throwable) {
                return null;
            }
        }

        return $this->safeActionUrl($payload['action_url'] ?? null);
    }
}

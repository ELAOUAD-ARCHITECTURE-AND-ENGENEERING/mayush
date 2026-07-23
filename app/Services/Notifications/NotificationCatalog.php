<?php

namespace App\Services\Notifications;

use InvalidArgumentException;

class NotificationCatalog
{
    public function get(string $eventKey): array
    {
        $definition = config('notifications_v2.events', [])[$eventKey] ?? null;

        if (!is_array($definition)) {
            throw new InvalidArgumentException("Unknown notification event [{$eventKey}].");
        }

        return array_merge([
            'type' => str_replace('.', '_', $eventKey),
            'category' => 'system',
            'severity' => 'info',
            'title' => 'Notification',
            'mandatory_inbox' => false,
            'channels' => ['in_app'],
        ], $definition);
    }

    public function all(): array
    {
        return config('notifications_v2.events', []);
    }

    public function mandatoryInbox(string $eventKey): bool
    {
        return (bool) $this->get($eventKey)['mandatory_inbox'];
    }
}

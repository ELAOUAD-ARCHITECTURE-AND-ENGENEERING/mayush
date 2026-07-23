<?php

namespace App\Services\Notifications;

use App\Models\NotificationDevice;
use App\Models\NotificationPreference;
use App\Models\NotificationType;
use App\Models\User;
use App\Models\UserNotificationSetting;
use Carbon\Carbon;

class NotificationPreferenceResolver
{
    public const CHANNELS = ['in_app', 'broadcast', 'mail', 'sms', 'push'];

    public function channels(User $user, string $eventKey, array $definition): array
    {
        $settings = UserNotificationSetting::firstOrNew(['user_id' => $user->id]);
        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('event_key', $eventKey)
            ->first();

        $enabled = [];
        foreach (self::CHANNELS as $channel) {
            $column = $channel === 'mail' ? 'email_enabled' : $channel.'_enabled';
            $catalogChannel = $channel === 'mail' ? 'email' : $channel;
            $default = in_array($catalogChannel, $definition['channels'], true)
                && (bool) ($settings->{$column} ?? true);
            $enabled[$channel] = $preference ? (bool) $preference->{$column} : $default;
        }

        if ($definition['mandatory_inbox']) {
            $enabled['in_app'] = true;
        }

        $enabled['broadcast'] = $enabled['broadcast']
            && (bool) config('notifications_v2.broadcasting_enabled');
        $enabled['sms'] = $enabled['sms']
            && (bool) config('notifications_v2.sms_enabled')
            && trim((string) $user->phone) !== '';
        $enabled['push'] = $enabled['push']
            && (bool) config('notifications_v2.fcm_enabled')
            && NotificationDevice::where('user_id', $user->id)->whereNull('revoked_at')->exists();
        $enabled['mail'] = $enabled['mail'] && filter_var($user->email, FILTER_VALIDATE_EMAIL);

        return $enabled;
    }

    public function notificationTypeId(array $definition): ?int
    {
        return NotificationType::where('type', $definition['type'])->value('id');
    }

    public function delayedUntil(User $user, array $definition, string $channel): ?Carbon
    {
        if (!in_array($channel, ['mail', 'sms', 'push'], true)
            || in_array($definition['severity'], ['critical', 'important'], true)) {
            return null;
        }

        $settings = UserNotificationSetting::where('user_id', $user->id)->first();
        if (!$settings?->quiet_hours_enabled || !$settings->quiet_hours_start || !$settings->quiet_hours_end) {
            return null;
        }

        try {
            $now = Carbon::now($settings->timezone ?: 'UTC');
        } catch (\Throwable) {
            $now = Carbon::now('UTC');
        }

        $start = $now->copy()->setTimeFromTimeString($settings->quiet_hours_start);
        $end = $now->copy()->setTimeFromTimeString($settings->quiet_hours_end);
        if ($end->lessThanOrEqualTo($start)) {
            if ($now->lessThan($end)) {
                $start->subDay();
            } else {
                $end->addDay();
            }
        }

        return $now->between($start, $end)
            ? $end->clone()->utc()
            : null;
    }
}

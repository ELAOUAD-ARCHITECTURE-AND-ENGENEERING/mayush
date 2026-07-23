<?php

namespace App\Services\Notifications;

use App\Models\NotificationDelivery;
use App\Models\NotificationDeliveryAttempt;
use Illuminate\Support\Str;
use Laravel\Pulse\Facades\Pulse;

class NotificationDeliveryRecorder
{
    public function transition(
        NotificationDelivery $delivery,
        string $state,
        ?string $providerCategory = null,
        ?string $failureCode = null,
        ?string $error = null,
        ?\DateTimeInterface $retryAt = null,
        ?int $attemptNumber = null
    ): void {
        $attemptNumber ??= max(0, (int) $delivery->attempt_count);

        $updates = [
            'state' => $state,
            'failure_code' => $failureCode,
        ];

        if ($state === 'sent') {
            $updates['sent_at'] = now();
        } elseif ($state === 'delivered') {
            $updates['delivered_at'] = now();
        } elseif ($state === 'failed') {
            $updates['failed_at'] = now();
        }

        $delivery->forceFill($updates)->save();

        NotificationDeliveryAttempt::firstOrCreate(
            [
                'delivery_id' => $delivery->id,
                'attempt_number' => $attemptNumber,
                'state' => $state,
            ],
            [
                'provider_response_category' => $providerCategory,
                'failure_code' => $failureCode,
                'sanitized_error' => $this->sanitizeError($error),
                'retry_at' => $retryAt,
                'occurred_at' => now(),
            ]
        );

        if (class_exists(Pulse::class)) {
            Pulse::record(
                'notification_delivery',
                $delivery->channel.':'.$state
            )->count()->onlyBuckets();
        }
    }

    private function sanitizeError(?string $error): ?string
    {
        if (!$error) {
            return null;
        }

        $error = preg_replace('/(bearer|token|key|secret|password)[=: ]+\S+/i', '$1=[redacted]', $error);

        return Str::limit((string) $error, 1000, '');
    }
}

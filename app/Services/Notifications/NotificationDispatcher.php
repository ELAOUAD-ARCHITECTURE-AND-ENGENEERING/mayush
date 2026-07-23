<?php

namespace App\Services\Notifications;

use App\Jobs\DeliverNotificationChannelJob;
use App\Models\NotificationDelivery;
use App\Models\NotificationEvent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Pulse\Facades\Pulse;

class NotificationDispatcher
{
    public function __construct(
        private NotificationCatalog $catalog,
        private NotificationPreferenceResolver $preferences,
        private NotificationPayload $payloads,
        private NotificationDeliveryRecorder $recorder
    ) {
    }

    public function dispatch(
        string $eventKey,
        string $sourceType,
        string|int|null $sourceId,
        string $occurrenceKey,
        iterable $recipients,
        array $payload = [],
        ?\DateTimeInterface $occurredAt = null
    ): void {
        if (!config('notifications_v2.enabled')) {
            return;
        }

        $recipientIds = collect($recipients)
            ->map(fn ($recipient) => $recipient instanceof User ? $recipient->id : $recipient)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $callback = fn () => $this->persist(
            $eventKey,
            $sourceType,
            (string) ($sourceId ?? ''),
            $occurrenceKey,
            $recipientIds,
            $payload,
            $occurredAt
        );

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($callback);
        } else {
            $callback();
        }
    }

    private function persist(
        string $eventKey,
        string $sourceType,
        string $sourceId,
        string $occurrenceKey,
        array $recipientIds,
        array $payload,
        ?\DateTimeInterface $occurredAt
    ): void {
        $definition = $this->catalog->get($eventKey);
        $safePayload = $this->payloads->safeEventPayload($payload);

        $event = $this->event(
            $eventKey,
            $sourceType,
            $sourceId,
            $occurrenceKey,
            $definition,
            $safePayload,
            $occurredAt
        );

        User::whereIn('id', $recipientIds)->get()->each(function (User $user) use ($event, $definition) {
            $this->createDeliveries($event, $user, $definition);
        });
    }

    private function event(
        string $eventKey,
        string $sourceType,
        string $sourceId,
        string $occurrenceKey,
        array $definition,
        array $payload,
        ?\DateTimeInterface $occurredAt
    ): NotificationEvent {
        $attributes = [
            'event_key' => $eventKey,
            'source_type' => mb_substr($sourceType, 0, 100),
            'source_id' => mb_substr($sourceId, 0, 100),
            'occurrence_key' => mb_substr($occurrenceKey, 0, 191),
        ];

        try {
            $event = NotificationEvent::firstOrCreate($attributes, [
                'id' => (string) Str::uuid(),
                'category' => $definition['category'],
                'severity' => $definition['severity'],
                'payload' => $payload,
                'occurred_at' => $occurredAt ?: now(),
            ]);
            if ($event->wasRecentlyCreated && class_exists(Pulse::class)) {
                Pulse::record('notification_event', $event->category.':'.$event->event_key)
                    ->count()
                    ->onlyBuckets();
            }

            return $event;
        } catch (QueryException $exception) {
            $existing = NotificationEvent::where($attributes)->first();
            if ($existing) {
                return $existing;
            }
            throw $exception;
        }
    }

    private function createDeliveries(NotificationEvent $event, User $user, array $definition): void
    {
        $enabled = $this->preferences->channels($user, $event->event_key, $definition);

        DB::transaction(function () use ($event, $user, $definition, $enabled) {
            foreach (NotificationPreferenceResolver::CHANNELS as $channel) {
                $notificationId = $channel === 'in_app' ? (string) Str::uuid() : null;
                $delay = $enabled[$channel]
                    ? $this->preferences->delayedUntil($user, $definition, $channel)
                    : null;

                $delivery = NotificationDelivery::firstOrCreate(
                    [
                        'event_id' => $event->id,
                        'recipient_type' => User::class,
                        'recipient_id' => $user->id,
                        'channel' => $channel,
                    ],
                    [
                        'notification_id' => $notificationId,
                        'state' => $enabled[$channel] ? 'queued' : 'disabled',
                        'scheduled_at' => $delay,
                        'failure_code' => $enabled[$channel] ? null : 'preference_or_unavailable',
                    ]
                );

                if (!$delivery->wasRecentlyCreated) {
                    continue;
                }

                if (!$enabled[$channel]) {
                    $this->recorder->transition(
                        $delivery,
                        'disabled',
                        'preference',
                        'preference_or_unavailable',
                        null,
                        null,
                        0
                    );
                    continue;
                }

                $this->recorder->transition($delivery, 'queued', 'application', null, null, $delay, 0);

                if ($channel === 'in_app') {
                    $this->createInboxProjection($event, $user, $delivery, $definition);
                    continue;
                }

                $job = new DeliverNotificationChannelJob($delivery->id);
                $job->onQueue($this->queueFor($channel));
                if ($delay) {
                    $job->delay($delay);
                }
                dispatch($job)->afterCommit();
            }
        });
    }

    private function createInboxProjection(
        NotificationEvent $event,
        User $user,
        NotificationDelivery $delivery,
        array $definition
    ): void {
        $unreadCount = $user->notifications()->whereNull('read_at')->whereNull('archived_at')->count() + 1;
        $snapshot = $this->payloads->inbox($event, $user, $delivery->notification_id, $unreadCount);

        DB::table('notifications')->insertOrIgnore([
            'id' => $delivery->notification_id,
            'event_id' => $event->id,
            'notification_type_id' => $this->preferences->notificationTypeId($definition),
            'type' => 'App\\Notifications\\CanonicalNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'category' => $event->category,
            'severity' => $event->severity,
            'data' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'read_at' => null,
            'archived_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->recorder->transition($delivery, 'delivered', 'database', null, null, null, 1);
    }

    private function queueFor(string $channel): string
    {
        return match ($channel) {
            'mail' => 'emails',
            'sms' => 'sms',
            'push' => 'push',
            default => 'notifications',
        };
    }
}

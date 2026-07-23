<?php

namespace App\Jobs;

use App\Events\NotificationInboxUpdated;
use App\Mail\GenericNotificationMail;
use App\Models\NotificationDelivery;
use App\Models\NotificationDevice;
use App\Models\User;
use App\Services\Notifications\FcmV1Service;
use App\Services\Notifications\NotificationCatalog;
use App\Services\Notifications\NotificationDeliveryRecorder;
use App\Services\Notifications\NotificationPayload;
use App\Services\Notifications\NotificationPreferenceResolver;
use App\Services\Notifications\PermanentNotificationFailure;
use App\Utility\SendSMSUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class DeliverNotificationChannelJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $deliveryId)
    {
        $this->onConnection('redis');
    }

    public function uniqueId(): string
    {
        return (string) $this->deliveryId;
    }

    public function backoff(): array
    {
        return [15 + random_int(0, 5), 60 + random_int(0, 15), 300 + random_int(0, 30)];
    }

    public function handle(
        NotificationDeliveryRecorder $recorder,
        NotificationPayload $payloads,
        NotificationCatalog $catalog,
        NotificationPreferenceResolver $preferences
    ): void {
        $delivery = NotificationDelivery::with('event')->find($this->deliveryId);
        if (!$delivery || in_array($delivery->state, ['disabled', 'sent', 'delivered'], true)) {
            return;
        }

        $user = User::find($delivery->recipient_id);
        if (!$user || !$delivery->event) {
            $recorder->transition($delivery, 'failed', 'application', 'recipient_or_event_missing');
            return;
        }

        $definition = $catalog->get($delivery->event->event_key);
        if (!($preferences->channels($user, $delivery->event->event_key, $definition)[$delivery->channel] ?? false)) {
            $recorder->transition($delivery, 'disabled', 'preference', 'preference_or_unavailable');
            return;
        }

        $attempt = max(1, $this->attempts());
        $delivery->forceFill(['attempt_count' => $attempt])->save();
        $payload = $payloads->inbox(
            $delivery->event,
            $user,
            (string) ($delivery->notification_id ?: $delivery->event_id),
            $user->notifications()->whereNull('read_at')->whereNull('archived_at')->count()
        );

        try {
            $reference = match ($delivery->channel) {
                'broadcast' => $this->broadcast($user, $payload),
                'mail' => $this->mail($user, $payload),
                'sms' => $this->sms($user, $payload),
                'push' => $this->push($user, $payload),
                default => throw new RuntimeException('Unsupported notification channel.'),
            };

            $delivery->forceFill(['provider_reference' => $reference])->save();
            $recorder->transition($delivery, 'sent', 'provider_accepted', null, null, null, $attempt);
        } catch (Throwable $exception) {
            $final = $exception instanceof PermanentNotificationFailure || $attempt >= $this->tries;
            $retryAt = $final ? null : now()->addSeconds($this->backoff()[$attempt - 1] ?? 300);
            $recorder->transition(
                $delivery,
                $final ? 'failed' : 'retrying',
                'provider_error',
                class_basename($exception),
                $exception->getMessage(),
                $retryAt,
                $attempt
            );

            Log::warning('Notification delivery attempt failed.', [
                'event_id' => $delivery->event_id,
                'delivery_id' => $delivery->id,
                'channel' => $delivery->channel,
                'attempt' => $attempt,
                'failure_code' => class_basename($exception),
            ]);

            if (!$final) {
                throw $exception;
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        $delivery = NotificationDelivery::find($this->deliveryId);
        if ($delivery && $delivery->state !== 'failed') {
            app(NotificationDeliveryRecorder::class)->transition(
                $delivery,
                'failed',
                'queue_exhausted',
                class_basename($exception),
                $exception->getMessage(),
                null,
                max(1, (int) $delivery->attempt_count)
            );
        }
    }

    private function broadcast(User $user, array $payload): string
    {
        broadcast(new NotificationInboxUpdated($user->id, $payload));

        return 'reverb';
    }

    private function mail(User $user, array $payload): string
    {
        Mail::to($user->email)->send(new GenericNotificationMail($payload));

        return 'mail';
    }

    private function sms(User $user, array $payload): string
    {
        SendSMSUtility::sendSMS($user->phone, (string) config('app.name'), $payload['message']);

        return 'sms';
    }

    private function push(User $user, array $payload): string
    {
        // Resolve the provider only after flags and preferences have allowed push.
        $fcm = app(FcmV1Service::class);
        $references = NotificationDevice::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->get()
            ->map(fn (NotificationDevice $device) => $fcm->send($device, $payload))
            ->filter()
            ->values();

        if ($references->isEmpty()) {
            throw new RuntimeException('No active notification device.');
        }

        return $references->implode(',');
    }
}

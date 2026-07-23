<?php

namespace Tests\Feature\Notifications;

use App\Jobs\DeliverNotificationChannelJob;
use App\Models\NotificationEvent;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\UserNotificationSetting;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationPayload;
use App\Services\Notifications\NotificationPreferenceResolver;
use App\Services\Notifications\NotificationCatalog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class NotificationCenterV2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        config()->set('notifications_v2.enabled', true);
        config()->set('notifications_v2.broadcasting_enabled', false);
        config()->set('notifications_v2.sms_enabled', false);
        config()->set('notifications_v2.fcm_enabled', false);
    }

    public function test_critical_event_always_creates_inbox_and_records_every_channel(): void
    {
        $user = User::factory()->customer()->create();
        UserNotificationSetting::create([
            'user_id' => $user->id,
            'in_app_enabled' => false,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);
        NotificationPreference::create([
            'user_id' => $user->id,
            'event_key' => 'payment.failed',
            'in_app_enabled' => false,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);

        $this->dispatch('payment.failed', $user, 'payment:100:failed');

        $event = NotificationEvent::firstOrFail();
        $this->assertSame('payment.failed', $event->event_key);
        $this->assertDatabaseCount('notification_deliveries', 5);
        $this->assertDatabaseHas('notification_deliveries', [
            'event_id' => $event->id,
            'recipient_id' => $user->id,
            'channel' => 'in_app',
            'state' => 'delivered',
        ]);
        $this->assertDatabaseHas('notifications', [
            'event_id' => $event->id,
            'notifiable_id' => $user->id,
            'category' => 'payments',
        ]);
        $this->assertSame(
            4,
            DB::table('notification_deliveries')
                ->where('event_id', $event->id)
                ->where('state', 'disabled')
                ->count()
        );
    }

    public function test_optional_disabled_event_is_audited_without_an_inbox_row(): void
    {
        $user = User::factory()->customer()->create();
        UserNotificationSetting::create([
            'user_id' => $user->id,
            'in_app_enabled' => false,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);

        $this->dispatch('marketing.promotion', $user, 'campaign:disabled');

        $this->assertDatabaseCount('notification_events', 1);
        $this->assertDatabaseCount('notification_deliveries', 5);
        $this->assertDatabaseCount('notifications', 0);
        $this->assertSame(5, DB::table('notification_deliveries')->where('state', 'disabled')->count());
    }

    public function test_repeated_occurrence_is_idempotent(): void
    {
        $user = User::factory()->customer()->create();
        UserNotificationSetting::create([
            'user_id' => $user->id,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);

        $this->dispatch('order.shipped', $user, 'tracking-history:77');
        $this->dispatch('order.shipped', $user, 'tracking-history:77');

        $this->assertDatabaseCount('notification_events', 1);
        $this->assertDatabaseCount('notification_deliveries', 5);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('notification_delivery_attempts', 6);
    }

    public function test_rolled_back_domain_transaction_does_not_create_an_event(): void
    {
        $user = User::factory()->customer()->create();

        try {
            DB::transaction(function () use ($user) {
                $this->dispatch('account.changed', $user, 'account:rolled-back');
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }

        $this->assertDatabaseCount('notification_events', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_api_actions_are_user_scoped_and_critical_preference_is_locked(): void
    {
        $user = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        UserNotificationSetting::create([
            'user_id' => $user->id,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);
        UserNotificationSetting::create([
            'user_id' => $other->id,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);
        $this->dispatch('security.alert', $user, 'security:user');
        $this->dispatch('security.alert', $other, 'security:other');
        $ownId = $user->notifications()->value('id');
        $otherId = $other->notifications()->value('id');

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v2/notifications/{$otherId}/read")
            ->assertNotFound();
        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v2/notifications/{$ownId}/read")
            ->assertOk()
            ->assertJsonPath('data.id', $ownId);
        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v2/notification-preferences', [
                'preferences' => [[
                    'event_key' => 'security.alert',
                    'in_app_enabled' => false,
                ]],
            ])
            ->assertStatus(422);
    }

    public function test_private_user_channel_authorization_is_user_scoped(): void
    {
        $user = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $callback = Broadcast::connection()->getChannels()->get('App.Models.User.{id}');

        $this->assertIsCallable($callback);
        $this->assertTrue($callback($user, (string) $user->id));
        $this->assertFalse($callback($user, (string) $other->id));
    }

    public function test_queued_channel_rechecks_a_disabled_flag_before_contacting_provider(): void
    {
        $user = User::factory()->customer()->create();
        config()->set('notifications_v2.broadcasting_enabled', true);

        $this->dispatch('payment.approved', $user, 'payment:queued-flag-check');
        $delivery = DB::table('notification_deliveries')
            ->where('channel', 'broadcast')
            ->first();

        config()->set('notifications_v2.broadcasting_enabled', false);
        app(DeliverNotificationChannelJob::class, ['deliveryId' => $delivery->id])
            ->handle(
                app(\App\Services\Notifications\NotificationDeliveryRecorder::class),
                app(\App\Services\Notifications\NotificationPayload::class),
                app(NotificationCatalog::class),
                app(NotificationPreferenceResolver::class)
            );

        $this->assertDatabaseHas('notification_deliveries', [
            'id' => $delivery->id,
            'state' => 'disabled',
            'failure_code' => 'preference_or_unavailable',
        ]);
    }

    public function test_action_urls_are_restricted_to_relative_or_same_origin_https(): void
    {
        config()->set('app.url', 'https://mayushdesign.com');
        $payloads = app(NotificationPayload::class);

        $this->assertSame('/purchase-history/123', $payloads->safeActionUrl('/purchase-history/123'));
        $this->assertSame(
            'https://mayushdesign.com/account',
            $payloads->safeActionUrl('https://mayushdesign.com/account')
        );
        $this->assertNull($payloads->safeActionUrl('http://mayushdesign.com/account'));
        $this->assertNull($payloads->safeActionUrl('https://attacker.example/phishing'));
        $this->assertNull($payloads->safeActionUrl('//attacker.example/phishing'));
    }

    public function test_quiet_hours_delay_only_noncritical_outbound_channels(): void
    {
        Carbon::setTestNow('2026-07-23 23:00:00 UTC');
        $this->beforeApplicationDestroyed(fn () => Carbon::setTestNow());
        $user = User::factory()->customer()->create();
        UserNotificationSetting::create([
            'user_id' => $user->id,
            'timezone' => 'UTC',
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00',
        ]);
        $resolver = app(NotificationPreferenceResolver::class);
        $catalog = app(NotificationCatalog::class);

        $optionalDelay = $resolver->delayedUntil(
            $user,
            $catalog->get('marketing.newsletter'),
            'mail'
        );
        $criticalDelay = $resolver->delayedUntil(
            $user,
            $catalog->get('security.alert'),
            'mail'
        );

        $this->assertSame('2026-07-24 07:00:00', $optionalDelay?->format('Y-m-d H:i:s'));
        $this->assertNull($criticalDelay);
        $this->assertNull($resolver->delayedUntil($user, $catalog->get('marketing.newsletter'), 'in_app'));
    }

    public function test_provider_delivery_callback_is_authenticated_and_records_true_delivery(): void
    {
        $user = User::factory()->customer()->create();
        UserNotificationSetting::create([
            'user_id' => $user->id,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);
        $this->dispatch('payment.approved', $user, 'payment:callback');
        $delivery = DB::table('notification_deliveries')
            ->where('channel', 'mail')
            ->first();
        DB::table('notification_deliveries')->where('id', $delivery->id)->update([
            'state' => 'sent',
            'attempt_count' => 1,
            'provider_reference' => 'provider-message-123',
            'sent_at' => now(),
        ]);
        config()->set('notifications_v2.delivery_webhook_secret', 'test-webhook-secret');

        $payload = [
            'provider_reference' => 'provider-message-123',
            'state' => 'delivered',
        ];
        $this->postJson('/api/v2/notification-delivery-webhooks/mail', $payload)
            ->assertUnauthorized();
        $this->withHeader('X-Notification-Webhook-Secret', 'test-webhook-secret')
            ->postJson('/api/v2/notification-delivery-webhooks/mail', $payload)
            ->assertOk()
            ->assertJsonPath('state', 'delivered');

        $this->assertDatabaseHas('notification_delivery_attempts', [
            'delivery_id' => $delivery->id,
            'attempt_number' => 1,
            'state' => 'delivered',
            'provider_response_category' => 'provider_callback',
        ]);
    }

    private function dispatch(string $eventKey, User $user, string $occurrence): void
    {
        app(NotificationDispatcher::class)->dispatch(
            $eventKey,
            'test',
            '1',
            $occurrence,
            [$user],
            [
                'title' => 'Test notification',
                'message' => 'Safe test message.',
                'action_url' => '/all-notifications',
            ]
        );
    }
}

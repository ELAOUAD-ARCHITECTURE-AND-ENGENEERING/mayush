<?php

namespace Tests\Feature\Notifications;

use App\Models\NotificationDevice;
use App\Models\NotificationPreference;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackfillNotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasColumn('users', 'device_token')) {
            Schema::table('users', function ($table) {
                $table->text('device_token')->nullable();
            });
        }
    }

    public function test_device_token_backfill_encrypts_tokens_and_is_safe_to_rerun(): void
    {
        $user = User::factory()->customer()->create();
        $token = 'legacy-device-token-'.Str::uuid();
        DB::table('users')->where('id', $user->id)->update(['device_token' => $token]);

        $this->artisan('notifications:backfill', ['--only' => 'devices', '--batch' => 1])
            ->assertExitCode(0);
        $this->artisan('notifications:backfill', ['--only' => 'devices', '--batch' => 1])
            ->assertExitCode(0);

        $this->assertDatabaseCount('notification_devices', 1);
        $stored = DB::table('notification_devices')->where('user_id', $user->id)->value('token');
        $this->assertNotSame($token, $stored);
        $this->assertSame($token, Crypt::decryptString($stored));
        $this->assertSame($token, NotificationDevice::firstOrFail()->token);
    }

    public function test_device_dry_run_does_not_write_or_expose_token(): void
    {
        $user = User::factory()->customer()->create();
        DB::table('users')->where('id', $user->id)->update(['device_token' => 'dry-run-token']);

        $this->artisan('notifications:backfill', [
            '--only' => 'devices',
            '--dry-run' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('notification_devices', 0);
    }

    public function test_backfill_preserves_admin_type_presentation_and_preferences(): void
    {
        $type = NotificationType::create([
            'type' => 'order_placed',
            'name' => 'Administrateur: commande',
            'image' => 'admin-order-icon.svg',
            'status' => 0,
            'default_text' => 'Texte administrateur',
            'category' => 'legacy-orders',
            'severity' => 'info',
            'mandatory_inbox' => false,
            'default_in_app' => false,
            'default_broadcast' => false,
            'default_email' => false,
            'default_sms' => false,
            'default_push' => false,
        ]);
        $user = User::factory()->customer()->create();
        $preference = NotificationPreference::create([
            'user_id' => $user->id,
            'event_key' => 'order.placed',
            'in_app_enabled' => false,
            'broadcast_enabled' => false,
            'email_enabled' => false,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);

        $this->artisan('notifications:backfill', ['--only' => 'types'])
            ->assertExitCode(0);

        $type->refresh();
        $this->assertSame('Administrateur: commande', $type->name);
        $this->assertSame('admin-order-icon.svg', $type->image);
        $this->assertSame('Texte administrateur', $type->default_text);
        $this->assertSame(0, (int) $type->status);
        $this->assertSame('orders', $type->category);
        $this->assertTrue((bool) $type->mandatory_inbox);
        $this->assertSame($preference->only([
            'user_id', 'event_key', 'in_app_enabled', 'broadcast_enabled',
            'email_enabled', 'sms_enabled', 'push_enabled',
        ]), $preference->fresh()->only([
            'user_id', 'event_key', 'in_app_enabled', 'broadcast_enabled',
            'email_enabled', 'sms_enabled', 'push_enabled',
        ]));
    }

    public function test_legacy_notification_backfill_normalizes_and_is_rerunnable(): void
    {
        $id = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'app\\Notifications\\OrderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory()->customer()->create()->id,
            'data' => '{}',
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('notifications:backfill', ['--only' => 'types'])
            ->assertExitCode(0);
        $this->artisan('notifications:backfill', ['--only' => 'notifications'])
            ->assertExitCode(0);
        $this->artisan('notifications:backfill', ['--only' => 'notifications'])
            ->assertExitCode(0);

        $record = DB::table('notifications')->where('id', $id)->first();
        $this->assertSame('App\\Notifications\\OrderNotification', $record->type);
        $this->assertNotNull($record->notification_type_id);
        $this->assertSame('orders', $record->category);
    }
}

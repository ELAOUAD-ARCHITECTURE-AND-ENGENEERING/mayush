<?php

namespace Tests\Feature\Customer;

use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_notification_page_shows_only_own_notifications_and_marks_them_read(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $type = $this->notificationType('Personal notification message');
        $ownId = $this->notification($customer, $type, ['link' => null]);
        $otherId = $this->notification($otherCustomer, $type, ['link' => null]);

        $this->actingAs($customer)
            ->get(route('all-notifications'))
            ->assertOk()
            ->assertSee('Personal notification message')
            ->assertDontSee($otherId);

        $this->assertNotNull(DB::table('notifications')->where('id', $ownId)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $otherId)->value('read_at'));
    }

    public function test_customer_notification_page_renders_empty_state(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('all-notifications'))
            ->assertOk()
            ->assertSee('No notification found');
    }

    public function test_customer_bulk_delete_only_deletes_current_users_notifications(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $type = $this->notificationType();
        $ownId = $this->notification($customer, $type);
        $otherId = $this->notification($otherCustomer, $type);

        $this->actingAs($customer)
            ->post(route('notifications.bulk_delete'), [
                'notification_ids' => [$ownId, $otherId],
            ])
            ->assertOk()
            ->assertSee('1');

        $this->assertDatabaseMissing('notifications', ['id' => $ownId]);
        $this->assertDatabaseHas('notifications', ['id' => $otherId]);
    }

    public function test_api_unread_mark_as_read_and_bulk_delete_are_user_scoped(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $type = $this->notificationType('API notification message');
        $ownId = $this->notification($customer, $type, ['link' => null]);
        $deleteId = $this->notification($customer, $type, ['link' => null]);
        $otherId = $this->notification($otherCustomer, $type, ['link' => null]);

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v2/unread-notifications')
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonFragment(['notification_text' => 'API notification message']);

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v2/notifications/mark-as-read/' . $ownId)
            ->assertOk()
            ->assertJsonPath('result', true);

        $this->assertNotNull(DB::table('notifications')->where('id', $ownId)->value('read_at'));

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v2/notifications/mark-as-read/' . $otherId)
            ->assertNotFound();

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v2/notifications/bulk-delete', [
                'notification_ids' => json_encode([$deleteId, $otherId]),
            ])
            ->assertOk()
            ->assertJsonPath('result', true);

        $this->assertDatabaseMissing('notifications', ['id' => $deleteId]);
        $this->assertDatabaseHas('notifications', ['id' => $otherId]);
    }

    private function notificationType(string $text = 'Notification message'): NotificationType
    {
        return NotificationType::create([
            'type' => 'custom',
            'name' => 'Customer Notification',
            'default_text' => $text,
            'status' => 1,
        ]);
    }

    private function notification(User $user, NotificationType $type, array $data = ['link' => null]): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'notification_type_id' => $type->id,
            'type' => 'App\Notifications\CustomNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode($data),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}

<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Mayush\Shipping\Onessta\Jobs\CreateShipmentJob;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Observers\OrderObserver;
use Tests\TestCase;

class OrderConfirmationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'onessta.enabled' => true,
            'onessta.queue.create_shipment_connection' => 'database',
            'onessta.queue.name' => 'onessta',
        ]);
        Cache::forget('addons');

        Addon::query()->updateOrCreate(
            ['unique_identifier' => 'onessta'],
            ['name' => 'ONESSTA', 'activated' => 1]
        );

        Order::observe(OrderObserver::class);
    }

    public function test_order_creation_dispatches_onessta_shipment(): void
    {
        Queue::fake();

        $order = Order::factory()->create([
            'shipping_type' => 'home_delivery',
            'is_confirmed' => false,
        ]);

        Queue::assertPushed(CreateShipmentJob::class, 1);
        Queue::assertPushed(CreateShipmentJob::class, function (CreateShipmentJob $job) use ($order) {
            return $job->orderId === $order->id
                && $job->shipmentData['code'] === 'ORD-' . $order->code;
        });

        $this->assertDatabaseHas('onessta_shipments', [
            'order_id' => $order->id,
            'code' => 'ORD-' . $order->code,
            'status' => 'QUEUED',
        ]);
    }

    public function test_finalized_checkout_order_dispatches_shipment_after_initial_order_save(): void
    {
        Queue::fake();

        // Mirror checkout: the order is created before shipping fields and
        // order details are written, then finalized in a later save.
        $order = Order::factory()->create([
            'shipping_type' => null,
            'grand_total' => 0,
        ]);

        Queue::assertNotPushed(CreateShipmentJob::class);

        OrderDetail::factory()->create([
            'order_id' => $order->id,
        ]);

        $order->forceFill([
            'shipping_type' => 'home_delivery',
            'grand_total' => 125,
        ])->save();

        app(\Mayush\Shipping\Onessta\Services\OrderShipmentDispatchService::class)
            ->ensureForOrder($order->fresh(['orderDetails', 'user']));

        Queue::assertPushed(CreateShipmentJob::class, 1);
        $this->assertDatabaseHas('onessta_shipments', [
            'order_id' => $order->id,
            'code' => 'ORD-' . $order->code,
            'status' => 'QUEUED',
        ]);
    }

    public function test_admin_confirmation_does_not_duplicate_creation_time_shipment(): void
    {
        Queue::fake();

        $order = Order::factory()->create([
            'shipping_type' => 'home_delivery',
            'is_confirmed' => false,
        ]);

        Queue::assertPushed(CreateShipmentJob::class, 1);

        $order->forceFill(['is_confirmed' => true])->save();

        Queue::assertPushed(CreateShipmentJob::class, 1);
    }

    public function test_existing_onessta_shipment_prevents_duplicate_dispatch(): void
    {
        Queue::fake();

        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'shipping_type' => 'home_delivery',
            'is_confirmed' => false,
        ]));

        OnesstaShipment::query()->create([
            'order_id' => $order->id,
            'code' => 'ORD-' . $order->code,
            'receiver' => 'Existing Customer',
            'phone' => '0600000000',
            'address' => 'Existing address',
            'city_id' => 1,
            'price' => $order->grand_total,
            'status' => 'WAITING_PICKUP',
        ]);

        Queue::assertNotPushed(CreateShipmentJob::class);
    }

    public function test_admin_confirmation_endpoint_validates_payload_and_updates_status(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'shipping_type' => 'home_delivery',
            'is_confirmed' => false,
        ]);

        Queue::assertPushed(CreateShipmentJob::class, 1);

        $this->actingAs($admin)
            ->postJson(route('orders.confirm'), [
                'order_id' => $order->id,
                'is_confirmed' => 1,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'is_confirmed' => true,
                'shipment' => [
                    'status' => 'exists',
                ],
            ]);

        $this->assertTrue($order->fresh()->is_confirmed);
        Queue::assertPushed(CreateShipmentJob::class, 1);

        $this->actingAs($admin)
            ->postJson(route('orders.confirm'), [
                'order_id' => $order->id,
                'is_confirmed' => 'not-a-boolean',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('is_confirmed');
    }

    public function test_confirmation_endpoint_retries_shipment_when_order_is_already_confirmed_without_shipment(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'shipping_type' => 'home_delivery',
            'is_confirmed' => true,
        ]));

        $this->actingAs($admin)
            ->postJson(route('orders.confirm'), [
                'order_id' => $order->id,
                'is_confirmed' => 1,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'is_confirmed' => true,
                'shipment' => [
                    'status' => 'queued',
                ],
            ]);

        Queue::assertPushed(CreateShipmentJob::class, 1);
    }

    public function test_non_admin_cannot_confirm_orders(): void
    {
        Queue::fake();

        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create(['is_confirmed' => false]);

        $this->actingAs($customer)
            ->postJson(route('orders.confirm'), [
                'order_id' => $order->id,
                'is_confirmed' => 1,
            ])
            ->assertNotFound();

        $this->assertFalse($order->fresh()->is_confirmed);
    }

    public function test_order_confirmation_dispatches_confirmation_email_only_on_first_confirmation(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        Queue::fake();

        if (!\Illuminate\Support\Facades\Schema::hasTable('email_templates')) {
            \Illuminate\Support\Facades\Schema::create('email_templates', function ($table) {
                $table->id();
                $table->string('identifier')->nullable();
                $table->string('subject')->nullable();
                $table->text('content')->nullable();
                $table->text('default_text')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        } elseif (!\Illuminate\Support\Facades\Schema::hasColumn('email_templates', 'default_text')) {
            \Illuminate\Support\Facades\Schema::table('email_templates', function ($table) {
                $table->text('default_text')->nullable();
            });
        }

        \DB::table('email_templates')->updateOrInsert(
            ['identifier' => 'order_confirmed_email_to_customer'],
            ['subject' => 'Confirmed [[order_code]]', 'default_text' => 'Order confirmed', 'status' => 1]
        );

        $admin = User::factory()->admin()->create();
        $buyer = User::factory()->customer()->create(['email' => 'buyer-confirm@example.test']);
        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'user_id' => $buyer->id,
            'seller_id' => $admin->id,
            'is_confirmed' => false,
            'shipping_type' => 'home_delivery',
        ]));

        // 1. Initial confirmation (false -> true): Email should be dispatched
        $this->actingAs($admin)
            ->postJson(route('orders.confirm'), [
                'order_id' => $order->id,
                'is_confirmed' => 1,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'is_confirmed' => true]);

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\MailManager::class, 2);

        // 2. Duplicate confirmation (true -> true): Email should NOT be dispatched again
        $this->actingAs($admin)
            ->postJson(route('orders.confirm'), [
                'order_id' => $order->id,
                'is_confirmed' => 1,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'is_confirmed' => true]);

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\MailManager::class, 2);

        // 3. Unconfirmation (true -> false): No email should be sent
        $this->actingAs($admin)
            ->postJson(route('orders.confirm'), [
                'order_id' => $order->id,
                'is_confirmed' => 0,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'is_confirmed' => false]);

        $this->assertFalse($order->fresh()->is_confirmed);
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\MailManager::class, 2);
    }
}

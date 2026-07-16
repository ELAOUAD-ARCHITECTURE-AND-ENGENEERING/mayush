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
}

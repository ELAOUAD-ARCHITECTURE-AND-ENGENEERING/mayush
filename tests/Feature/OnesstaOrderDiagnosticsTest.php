<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Mayush\Shipping\Onessta\Jobs\CreateShipmentJob;
use Mayush\Shipping\Onessta\Models\OnesstaCityMap;
use Tests\TestCase;

class OnesstaOrderDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('addons');

        config([
            'onessta.enabled' => true,
            'onessta.auth.token' => 'test-token',
            'onessta.auth.api_key' => 'test-api-key',
            'onessta.auth.client_id' => 'test-client-id',
            'onessta.queue.create_shipment_connection' => 'database',
            'onessta.queue.name' => 'onessta',
        ]);

        Addon::query()->updateOrCreate(
            ['unique_identifier' => 'onessta'],
            ['name' => 'ONESSTA', 'activated' => 1]
        );
    }

    public function test_diagnose_order_reports_blockers_without_dispatching(): void
    {
        Queue::fake();
        config(['onessta.enabled' => false]);

        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'shipping_type' => 'flat_rate',
            'shipping_method' => 'flat_rate',
        ]));

        $this->artisan('onessta:diagnose-order', ['order' => $order->id])
            ->expectsOutputToContain('ONESSTA integration: disabled')
            ->expectsOutputToContain('Blocker: set ONESSTA_ENABLED=true.')
            ->expectsOutputToContain('Blocker: order shipping method/type is not ONESSTA or home_delivery.')
            ->assertExitCode(1);

        Queue::assertNotPushed(CreateShipmentJob::class);
    }

    public function test_diagnose_order_dispatches_eligible_order_through_normal_service(): void
    {
        Queue::fake();

        OnesstaCityMap::query()->create([
            'remote_city_id' => 123,
            'remote_city_name' => 'Remote Rabat',
            'local_city_id' => 10,
            'local_city_name' => 'Rabat',
            'active' => true,
        ]);

        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'shipping_type' => 'home_delivery',
            'shipping_method' => 'home_delivery',
            'shipping_address' => json_encode([
                'name' => 'Diagnostic Customer',
                'phone' => '0600000000',
                'address' => '1 Test Street',
                'city_id' => 10,
                'city' => 'Rabat',
            ]),
        ]));

        $this->artisan('onessta:diagnose-order', [
            'order' => $order->id,
            '--dispatch' => true,
        ])
            ->expectsOutputToContain('City mapping: local 10 -> ONESSTA 123')
            ->expectsOutputToContain('Status: queued')
            ->expectsOutputToContain('Queue connection: database')
            ->assertExitCode(0);

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
}

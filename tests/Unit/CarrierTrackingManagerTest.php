<?php

namespace Tests\Unit;

use App\Services\Logistics\CarrierTrackingManager;
use App\Services\Logistics\MockShippingCarrier;
use RuntimeException;
use Tests\TestCase;

class CarrierTrackingManagerTest extends TestCase
{
    public function test_testing_environment_resolves_mock_carrier_by_default(): void
    {
        config(['logistics.mock_carrier_enabled' => true]);

        $carrier = (new CarrierTrackingManager())->resolveCarrier();

        $this->assertInstanceOf(MockShippingCarrier::class, $carrier);
    }

    public function test_production_blocks_mock_carrier_by_default(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['logistics.mock_carrier_enabled' => false]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No production carrier integration is configured');

        (new CarrierTrackingManager())->resolveCarrier();
    }

    public function test_production_can_explicitly_enable_mock_carrier(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['logistics.mock_carrier_enabled' => true]);

        $carrier = (new CarrierTrackingManager())->resolveCarrier();

        $this->assertInstanceOf(MockShippingCarrier::class, $carrier);
    }
}

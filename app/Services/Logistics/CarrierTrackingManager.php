<?php

namespace App\Services\Logistics;

use RuntimeException;

/**
 * Class CarrierTrackingManager
 * Dynamically resolves internal shipping carrier implementations based on configured integrations or DB settings.
 */
class CarrierTrackingManager
{
    /**
     * Resolves the appropriate implementation for the provided Carrier ID or Code.
     */
    public function resolveCarrier($carrierId = null): ShippingCarrierInterface
    {
        // Future scale: match $carrierId to FedEx, DHL, USPS, etc..
        $mockEnabled = config('logistics.mock_carrier_enabled') || app()->environment(['local', 'testing']);

        if (!$mockEnabled) {
            throw new RuntimeException('No production carrier integration is configured for order tracking.');
        }

        return new MockShippingCarrier();
    }
}

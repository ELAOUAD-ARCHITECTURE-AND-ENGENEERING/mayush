<?php

namespace App\Services\Logistics;

/**
 * Class CarrierTrackingManager
 * Dynamically resolves internal shipping carrier implementations based on configured integrations or DB settings.
 */
class CarrierTrackingManager
{
    /**
     * Resolves the appropriate implementation for the provided Carrier ID or Code.
     * Currently always defaults to MockShippingCarrier since we don't have active Carrier API credentials yet.
     */
    public function resolveCarrier($carrierId = null): ShippingCarrierInterface
    {
        // Future scale: match $carrierId to FedEx, DHL, USPS, etc..
        return new MockShippingCarrier();
    }
}

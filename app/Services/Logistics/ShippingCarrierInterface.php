<?php

namespace App\Services\Logistics;

/**
 * Interface ShippingCarrierInterface
 * Enforces a strict contract for integrating multiple shipping providers.
 */
interface ShippingCarrierInterface
{
    /**
     * Fetch tracking information for a given tracking code from the carrier's API.
     * 
     * @param string $trackingCode
     * @return array Contains status, location_name, lat, lng, notes, expected_date
     */
    public function fetchTrackingInfo(string $trackingCode): array;
}

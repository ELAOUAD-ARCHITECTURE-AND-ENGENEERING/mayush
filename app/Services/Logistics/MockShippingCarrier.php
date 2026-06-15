<?php

namespace App\Services\Logistics;

/**
 * Class MockShippingCarrier
 * A default/mock implementation of the interface for testing and simulation.
 */
class MockShippingCarrier implements ShippingCarrierInterface
{
    public function fetchTrackingInfo(string $trackingCode): array
    {
        // Simulate a successful API response logic based on the code randomly hashing
        $hash = crc32($trackingCode);
        $modulo = $hash % 5;
        
        $statuses = ['processing', 'shipped', 'in_transit', 'out_for_delivery', 'delivered'];
        $locations = ['Warehouse Hub', 'Logistics Center', 'Local Sorting Facility', 'Delivery Van', 'Customer Address'];
        $notes = ['Package is being prepared', 'Package handed over to logistics', 'Package in transit between hubs', 'Package is out for delivery', 'Package has been delivered to the customer'];
        
        return [
            'status' => $statuses[$modulo],
            'location_name' => $locations[$modulo],
            'latitude' => 34.0522 + ($modulo * 0.01), // mock coordinate updates
            'longitude' => -118.2437 + ($modulo * 0.01),
            'notes' => $notes[$modulo],
            'expected_delivery_date' => now()->addDays(2),
        ];
    }
}

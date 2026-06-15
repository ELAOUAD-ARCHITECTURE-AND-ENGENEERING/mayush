<?php

namespace Mayush\Shipping\Onessta\Helpers;

use Mayush\Shipping\Onessta\Models\OnesstaCityMap;
use Mayush\Shipping\Onessta\Exceptions\CityMappingException;

class CityMappingHelper
{
    /**
     * Normalize and validate city mapping.
     * 
     * @param int|string $cityIdentifier
     * @return int
     * @throws CityMappingException
     */
    public static function getRemoteCityId($cityIdentifier): int
    {
        $query = OnesstaCityMap::active();

        if (is_numeric($cityIdentifier)) {
            $map = $query->where(function($q) use ($cityIdentifier) {
                $q->where('local_city_id', $cityIdentifier)
                  ->orWhere('remote_city_id', $cityIdentifier);
            })->first();
        } else {
            $map = $query->where('remote_city_name', 'LIKE', trim($cityIdentifier))->first();
        }

        if (!$map || !$map->remote_city_id) {
            throw new CityMappingException("City identifier '{$cityIdentifier}' is not mapped to a valid ONESSTA city.");
        }

        return (int) $map->remote_city_id;
    }

    /**
     * Check if a city is correctly mapped.
     */
    public static function isMapped(int $localCityId): bool
    {
        return OnesstaCityMap::active()->byLocalCityId($localCityId)->exists();
    }
}

<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\DTOs\CityDto;
use Mayush\Shipping\Onessta\DTOs\PickupCityDto;
use Mayush\Shipping\Onessta\Models\OnesstaCityMap;

class ReferenceDataService
{
    private const CACHE_KEY_CITIES = 'onessta:cities:all';
    private const CACHE_KEY_PICKUP_CITIES = 'onessta:pickup_cities:all';

    private OnesstaClient $client;

    public function __construct(OnesstaClient $client)
    {
        $this->client = $client;
    }

    public function syncCities(bool $force = false): array
    {
        $cacheKey = self::CACHE_KEY_CITIES;

        if (!$force && Cache::has($cacheKey)) {
            Log::info('ONESSTA cities cache hit, skipping sync');
            return Cache::get($cacheKey);
        }

        Log::info('ONESSTA: Syncing cities from remote API');
        $response = $this->client->get('/p/cities');

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch cities from ONESSTA: ' . $response->status());
        }

        $raw = $response->json();
        $cities = $raw['data']['cities'] ?? $raw;
        $ttl = config('onessta.cache.ttl_cities', 86400);

        $cityDtos = collect($cities)->map(fn(array $item) => CityDto::fromArray($item)->toArray())->toArray();

        Cache::put($cacheKey, $cityDtos, $ttl);

        $this->persistCityMaps($cityDtos);

        Log::info('ONESSTA: Cities synced successfully', ['count' => count($cityDtos)]);

        return $cityDtos;
    }

    public function syncPickupCities(bool $force = false): array
    {
        $cacheKey = self::CACHE_KEY_PICKUP_CITIES;

        if (!$force && Cache::has($cacheKey)) {
            Log::info('ONESSTA pickup cities cache hit, skipping sync');
            return Cache::get($cacheKey);
        }

        Log::info('ONESSTA: Syncing pickup cities from remote API');
        $response = $this->client->get('/p/pickup_cities');

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch pickup cities from ONESSTA: ' . $response->status());
        }

        $raw = $response->json();
        $cities = $raw['data']['cities'] ?? $raw;
        $ttl = config('onessta.cache.ttl_pickup_cities', 86400);

        $cityDtos = collect($cities)->map(fn(array $item) => PickupCityDto::fromArray($item)->toArray())->toArray();

        Cache::put($cacheKey, $cityDtos, $ttl);

        $this->persistPickupCityMaps($cityDtos);

        Log::info('ONESSTA: Pickup cities synced successfully', ['count' => count($cityDtos)]);

        return $cityDtos;
    }

    public function resolveCity(int $localCityId): ?int
    {
        $map = OnesstaCityMap::active()
            ->byLocalCityId($localCityId)
            ->first();

        return $map?->remote_city_id;
    }

    public function resolveLocalCity(int $remoteCityId): ?int
    {
        $map = OnesstaCityMap::active()
            ->byRemoteCityId($remoteCityId)
            ->first();

        return $map?->local_city_id;
    }

    public function resolvePickupCity(int $localCityId): ?int
    {
        $map = \Mayush\Shipping\Onessta\Models\OnesstaPickupCityMap::active()
            ->byLocalCityId($localCityId)
            ->first();

        return $map?->remote_city_id;
    }

    public function getCachedCities(): array
    {
        return Cache::get(self::CACHE_KEY_CITIES, []);
    }

    public function getCachedPickupCities(): array
    {
        return Cache::get(self::CACHE_KEY_PICKUP_CITIES, []);
    }

    public function flushCitiesCache(): void
    {
        Cache::forget(self::CACHE_KEY_CITIES);
        Log::info('ONESSTA: Cities cache flushed');
    }

    public function flushPickupCitiesCache(): void
    {
        Cache::forget(self::CACHE_KEY_PICKUP_CITIES);
        Log::info('ONESSTA: Pickup cities cache flushed');
    }

    private function persistCityMaps(array $cities): void
    {
        foreach ($cities as $city) {
            OnesstaCityMap::updateOrCreate(
                ['remote_city_id' => $city['id']],
                [
                    'remote_city_name' => $city['name'],
                    'active' => true,
                ]
            );
        }
    }

    private function persistPickupCityMaps(array $cities): void
    {
        $pickupMapModel = \Mayush\Shipping\Onessta\Models\OnesstaPickupCityMap::class;

        foreach ($cities as $city) {
            $pickupMapModel::updateOrCreate(
                ['remote_city_id' => $city['id']],
                [
                    'remote_city_name' => $city['name'],
                    'active' => true,
                ]
            );
        }

        OnesstaCityMap::where('is_pickup', true)->update(['is_pickup' => false]);
        foreach ($cities as $city) {
            OnesstaCityMap::where('remote_city_id', $city['id'])->update(['is_pickup' => true]);
        }
    }
}

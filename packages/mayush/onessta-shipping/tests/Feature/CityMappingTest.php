<?php

namespace Mayush\Shipping\Onessta\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mayush\Shipping\Onessta\Models\OnesstaCityMap;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;

class CityMappingTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        if (!env('TESTING_WITH_DB', false)) {
            $this->markTestSkipped('Database tests require TESTING_WITH_DB=true and MySQL connection');
        }
        parent::setUp();
    }

    public function test_sync_cities_populates_mapping_table(): void
    {
        Http::fake([
            '*' => Http::response([
                ['id' => 1, 'name' => 'City One'],
                ['id' => 2, 'name' => 'City Two'],
                ['id' => 3, 'name' => 'City Three'],
            ], 200),
        ]);

        Cache::forget('onessta:cities:all');

        $client = new \Mayush\Shipping\Onessta\Client\OnesstaClient(
            'https://api.onessta.com/api/v1',
            'test-token',
            'test-api-key',
            'test-client-id'
        );

        $service = new ReferenceDataService($client);
        $cities = $service->syncCities(force: true);

        $this->assertCount(3, $cities);
        $this->assertEquals('City One', $cities[0]['name']);

        $this->assertDatabaseHas('onessta_city_maps', [
            'remote_city_id' => 1,
            'remote_city_name' => 'City One',
            'active' => true,
        ]);

        $this->assertDatabaseHas('onessta_city_maps', [
            'remote_city_id' => 2,
            'remote_city_name' => 'City Two',
        ]);
    }

    public function test_resolve_city_returns_remote_id_after_sync(): void
    {
        OnesstaCityMap::firstOrCreate(
            ['remote_city_id' => 42],
            [
                'remote_city_name' => 'Mapped City',
                'local_city_id' => 5,
                'local_city_name' => 'Local City',
                'active' => true,
            ]
        );

        $client = new \Mayush\Shipping\Onessta\Client\OnesstaClient(
            'https://api.onessta.com/api/v1',
            'test-token',
            'test-api-key',
            'test-client-id'
        );

        $service = new ReferenceDataService($client);
        $remoteId = $service->resolveCity(5);

        $this->assertEquals(42, $remoteId);
    }

    public function test_resolve_city_returns_null_for_unmapped(): void
    {
        $client = new \Mayush\Shipping\Onessta\Client\OnesstaClient(
            'https://api.onessta.com/api/v1',
            'test-token',
            'test-api-key',
            'test-client-id'
        );

        $service = new ReferenceDataService($client);
        $remoteId = $service->resolveCity(9999);

        $this->assertNull($remoteId);
    }

    public function test_duplicate_sync_does_not_create_duplicates(): void
    {
        Http::fake([
            '*' => Http::response([
                ['id' => 10, 'name' => 'Dup City'],
            ], 200),
        ]);

        $client = new \Mayush\Shipping\Onessta\Client\OnesstaClient(
            'https://api.onessta.com/api/v1',
            'test-token',
            'test-api-key',
            'test-client-id'
        );

        $service = new ReferenceDataService($client);
        $service->syncCities(force: true);
        $service->syncCities(force: true);

        $count = OnesstaCityMap::where('remote_city_id', 10)->count();
        $this->assertEquals(1, $count);
    }
}

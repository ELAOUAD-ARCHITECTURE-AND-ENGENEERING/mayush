<?php

namespace Tests\Unit\Services;

use App\Services\SearchTelemetry;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SearchTelemetryTest extends TestCase
{
    public function test_telemetry_logs_hash_and_omits_raw_query_by_default(): void
    {
        $matched = false;
        Log::shouldReceive('info')
            ->once()
            ->with('search.telemetry', Mockery::on(function (array $payload) use (&$matched): bool {
                $matched = isset($payload['query_hash'])
                    && $payload['query_length'] === 18
                    && $payload['result_count'] === 3
                    && !array_key_exists('query', $payload);

                return true;
            }));
        config([
            'search.telemetry.enabled' => true,
            'search.telemetry.sample_rate' => 1.0,
            'search.telemetry.store_raw_query' => false,
        ]);

        app(SearchTelemetry::class)->record('search.completed', [
            'query' => 'chaise confortable',
            'locale' => 'fr',
            'result_count' => 3,
            'duration_ms' => 42.5,
        ]);

        $this->assertTrue($matched);
    }

    public function test_disabled_telemetry_does_not_log(): void
    {
        Log::shouldReceive('info')->never();
        config(['search.telemetry.enabled' => false]);

        app(SearchTelemetry::class)->record('search.completed', ['query' => 'table']);

        $this->assertTrue(true);
    }
}

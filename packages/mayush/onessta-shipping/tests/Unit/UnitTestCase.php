<?php

namespace Mayush\Shipping\Onessta\Tests\Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Mayush\Shipping\Onessta\Tests\CreatesApplication;

abstract class UnitTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config(['onessta.enabled' => true]);
        config(['onessta.mode' => 'live']);
        config(['onessta.base_url' => 'https://api.onessta.com/api/v1']);
        config(['onessta.auth.token' => 'test-token']);
        config(['onessta.auth.api_key' => 'test-api-key']);
        config(['onessta.auth.client_id' => 'test-client-id']);
        config(['onessta.http.timeout' => 30]);
        config(['onessta.http.connect_timeout' => 10]);
        config(['onessta.http.retry_times' => 3]);
        config(['onessta.http.retry_sleep_ms' => 500]);
        config(['onessta.webhook.secret' => 'webhook-secret-key']);
        config(['onessta.webhook.fail_on_signature_mismatch' => true]);
        config(['onessta.cache.ttl_cities' => 86400]);
        config(['onessta.cache.ttl_pickup_cities' => 86400]);
        config(['onessta.cache.ttl_tracking' => 300]);
        config(['onessta.capabilities.quotes' => false]);
        config(['onessta.capabilities.labels' => false]);
        config(['onessta.capabilities.products' => true]);
        config(['onessta.capabilities.stock' => true]);
    }
}

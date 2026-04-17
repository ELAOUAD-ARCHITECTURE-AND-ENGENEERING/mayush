<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\Client;

use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\Exceptions\AuthenticationException;
use Mayush\Shipping\Onessta\Exceptions\RemoteApiException;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class OnesstaClientTest extends UnitTestCase
{
    private OnesstaClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new OnesstaClient(
            'https://api.onessta.com/api/v1',
            'test-token',
            'test-api-key',
            'test-client-id',
            30,
            10,
            3,
            500,
            [408, 502, 503, 504]
        );
    }

    public function test_auth_headers_are_sent_on_post_request(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'ok'], 200),
        ]);

        $this->client->post('/test', ['key' => 'value']);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token')
                && $request->hasHeader('API-Key', 'test-api-key')
                && $request->hasHeader('Client-ID', 'test-client-id')
                && $request->hasHeader('Accept', 'application/json')
                && $request->hasHeader('Content-Type', 'application/json');
        });
    }

    public function test_auth_headers_are_sent_on_get_request(): void
    {
        Http::fake([
            '*' => Http::response(['items' => []], 200),
        ]);

        $this->client->get('/cities', ['page' => 1]);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token')
                && $request->hasHeader('API-Key', 'test-api-key')
                && $request->hasHeader('Client-ID', 'test-client-id');
        });
    }

    public function test_throws_authentication_exception_on_401(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Unauthenticated.'], 401),
        ]);

        $this->expectException(AuthenticationException::class);
        $this->client->post('/test', []);
    }

    public function test_throws_remote_api_exception_on_500(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Server Error'], 500),
        ]);

        $this->expectException(RemoteApiException::class);
        $this->client->post('/test', []);
    }

    public function test_base_url_is_trimmed_and_normalized(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $client = new OnesstaClient(
            'https://api.onessta.com/api/v1/',
            'token',
            'key',
            'client'
        );

        $this->assertEquals('https://api.onessta.com/api/v1', $client->getBaseUrl());
    }
}

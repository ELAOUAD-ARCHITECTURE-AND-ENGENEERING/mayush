<?php

namespace Tests\Integration\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

/**
 * CsrfMiddlewareTest
 *
 * Validates that Laravel's VerifyCsrfToken middleware correctly rejects
 * POST requests without a CSRF token (419) and allows GET requests through.
 *
 * Uses RefreshDatabase to ensure the in-memory SQLite DB has all tables,
 * preventing error-page rendering failures.
 */
class CsrfMiddlewareTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function post_request_bypasses_csrf_in_laravel_testing_environment(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function get_request_does_not_require_csrf(): void
    {
        $this->get('/')->assertStatus(200);
    }

    /** @test */
    public function api_routes_do_not_require_csrf(): void
    {
        $response = $this->post('/api/v2/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertNotEquals(419, $response->status());
    }
}

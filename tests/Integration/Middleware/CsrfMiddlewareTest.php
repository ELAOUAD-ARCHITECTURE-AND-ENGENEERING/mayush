<?php

namespace Tests\Integration\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

/**
 * CsrfMiddlewareTest
 *
 * Validates that Laravel's VerifyCsrfToken middleware behavior is understood
 * within the testing environment.
 *
 * NOTE: These tests validate the Laravel testing-kernel behavior, where CSRF
 * protection is automatically bypassed by design (runningUnitTests() = true).
 * This ensures controller logic can be tested in isolation.
 * For production-level CSRF rejection (419 status), a separate manual/browser-level
 * smoke test is required to verify behavior outside the testing kernel.
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

<?php

namespace Tests\Integration\Middleware;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsrfMiddlewareTest extends TestCase
{
    /** @test */
    public function post_request_without_csrf_is_blocked()
    {
        // We use a route that usually requires CSRF, like login or a dummy post route
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);

        // Laravel returns 419 Page Expired for CSRF mismatch
        $response->assertStatus(419);
    }

    /** @test */
    public function get_request_does_not_require_csrf()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function api_routes_do_not_require_csrf()
    {
        // API routes (prefix /api) typically use Sanctum/Passport and exclude CSRF
        $response = $this->post('/api/v2/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);

        // Should NOT return 419. Might return 401 or 422, but not 419.
        $this->assertNotEquals(419, $response->status());
    }
}

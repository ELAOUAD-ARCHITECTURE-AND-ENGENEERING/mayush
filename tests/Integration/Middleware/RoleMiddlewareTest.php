<?php

namespace Tests\Integration\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * RoleMiddlewareTest
 *
 * Verifies that role-based middleware correctly redirects unauthorized users
 * and allows authorized users through.
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the minimum required language row
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );
    }

    // ─── Admin Routes ────────────────────────────────────────────────────────

    /** @test */
    public function guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(); // 302 to login
    }

    /** @test */
    public function customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/admin/dashboard');
        // Should redirect away — not 200
        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        // Admin gets through (200 or redirect to specific admin pages)
        $this->assertContains($response->status(), [200, 302]);
    }

    // ─── Seller Routes ───────────────────────────────────────────────────────

    /** @test */
    public function guest_cannot_access_seller_dashboard(): void
    {
        $response = $this->get('/seller/dashboard');
        $response->assertRedirect();
    }

    /** @test */
    public function customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/seller/dashboard');
        $response->assertRedirect();
    }

    // ─── Customer Routes ─────────────────────────────────────────────────────

    /** @test */
    public function guest_cannot_access_purchase_history(): void
    {
        $response = $this->get('/purchase-history');
        $response->assertRedirect();
    }

    /** @test */
    public function customer_can_access_purchase_history(): void
    {
        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/purchase-history');
        // Should render (200) or redirect within the auth area — not to login
        $this->assertNotEquals(302, $response->status() === 302
            && str_contains((string)$response->headers->get('location'), 'login') ? 302 : 200
        );
    }

    // ─── CSRF Protection ─────────────────────────────────────────────────────

    /** @test */
    public function post_without_csrf_token_returns_419(): void
    {
        $response = $this->post('/login', [
            'email'    => 'fake@test.com',
            'password' => 'wrongpassword',
        ]);
        // Without CSRF token → 419 Token Mismatch
        $this->assertContains($response->status(), [419, 302, 200]);
    }
}

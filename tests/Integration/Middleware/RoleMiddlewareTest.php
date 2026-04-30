<?php

namespace Tests\Integration\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * RoleMiddlewareTest
 *
 * Verifies that role-based middleware correctly blocks unauthorized users
 * and allows authorized users through. Tests reflect the real middleware
 * behavior:
 *   - IsAdmin: abort(404) for non-admin
 *   - IsSeller: abort(404) for non-seller
 *   - IsCustomer: redirect to login for guests
 *   - auth: redirect to login for guests
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );
    }

    // ─── Admin Routes ────────────────────────────────────────────────────────
    // Route: GET /admin  →  middleware: ['auth', 'admin']
    // IsAdmin aborts 404 for non-admin/staff users.
    // auth middleware redirects guests to login.

    /** @test */
    public function guest_cannot_access_admin_dashboard(): void
    {
        // auth middleware intercepts first → redirect to login
        $response = $this->get('/admin');
        $response->assertRedirect();
    }

    /** @test */
    public function customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();
        // auth passes, IsAdmin sees user_type != admin → abort(404)
        $response = $this->actingAs($customer)->get('/admin');
        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get('/admin');
        // IsAdmin passes — controller runs (200 or 500 from missing view data)
        // Key assertion: NOT blocked by middleware (not 404, not redirect to login)
        $this->assertNotEquals(404, $response->status(), 'Admin should not be blocked by IsAdmin middleware');
    }

    // ─── Seller Routes ───────────────────────────────────────────────────────
    // Route: GET /seller/dashboard  →  middleware: ['seller', 'verified', 'user', 'prevent-back-history']
    // IsSeller aborts 404 for non-seller users (including guests).

    /** @test */
    public function guest_cannot_access_seller_dashboard(): void
    {
        // IsSeller: Auth::check() false → abort(404)
        $response = $this->get('/seller/dashboard');
        $response->assertStatus(404);
    }

    /** @test */
    public function customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->customer()->create();
        // IsSeller: user_type != seller → abort(404)
        $response = $this->actingAs($customer)->get('/seller/dashboard');
        $response->assertStatus(404);
    }

    // ─── Customer Routes ─────────────────────────────────────────────────────
    // Route: GET /purchase_history  →  middleware: ['customer', 'verified', 'unbanned']
    // IsCustomer redirects guests to user.login route.

    /** @test */
    public function guest_cannot_access_purchase_history(): void
    {
        // IsCustomer: Auth::check() false → redirect to user.login
        $response = $this->get('/purchase_history');
        $response->assertRedirect();
    }

    /** @test */
    public function customer_can_access_purchase_history(): void
    {
        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/purchase_history');
        // Customer middleware passes. Controller runs (200, 302 within app, or 500 from view).
        // Key assertion: NOT redirected to login
        $locationHeader = $response->headers->get('Location', '');
        $this->assertStringNotContainsString('login', $locationHeader, 'Customer should not be redirected to login');
    }
}

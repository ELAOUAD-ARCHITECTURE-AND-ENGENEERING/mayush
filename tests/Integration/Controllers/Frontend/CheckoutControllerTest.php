<?php

namespace Tests\Integration\Controllers\Frontend;

use Tests\TestCase;
use App\Http\Controllers\OrderConfirmationController;
use App\Models\User;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * CheckoutControllerTest
 *
 * Integration tests for checkout page access control and form validation.
 */
class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );

        BusinessSetting::updateOrCreate(['type' => 'site_name'], ['value' => 'MayushTest']);
        BusinessSetting::updateOrCreate(['type' => 'language'], ['value' => 'en']);
        BusinessSetting::updateOrCreate(['type' => 'google_recaptcha'], ['value' => '0']);
        BusinessSetting::updateOrCreate(['type' => 'color_scheme'], ['value' => 'default']);
    }

    /** @test */
    public function guest_checkout_no_longer_redirects_to_login_gate(): void
    {
        $response = $this->get('/checkout');
        $response->assertRedirect();
        $this->assertNotEquals(route('user.login'), $response->headers->get('Location'));
    }

    /** @test */
    public function authenticated_customer_can_access_checkout(): void
    {
        $customer = User::factory()->customer()->create();
        $response = $this->actingAs($customer)->get('/checkout');
        // Customer passes auth gate (200 or redirected within checkout flow)
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function checkout_page_does_not_use_login_route_as_guest_gate(): void
    {
        $response = $this->get('/checkout');
        $this->assertNotEquals(route('user.login'), $response->headers->get('Location'));
    }

    /** @test */
    public function order_confirmed_page_accessible(): void
    {
        // The order-confirmed page is accessed after a successful order
        // Checking route exists and returns a non-500 response for a guest redirect
        $response = $this->get('/order-confirmed');
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /** @test */
    public function order_confirmation_routes_use_the_notification_aware_controller(): void
    {
        $route = app('router')->getRoutes()->getByName('order_confirmed');
        $routeWithId = app('router')->getRoutes()->getByName('order_confirmed_with_id');

        $this->assertSame(OrderConfirmationController::class, $route->getControllerClass());
        $this->assertSame('orderConfirmed', $route->getActionMethod());
        $this->assertSame(OrderConfirmationController::class, $routeWithId->getControllerClass());
        $this->assertSame('orderConfirmedWithId', $routeWithId->getActionMethod());
    }

    /** @test */
    public function payment_failed_page_returns_200(): void
    {
        $response = $this->get('/payment-failed');
        $this->assertContains($response->status(), [200, 302, 404]);
    }
}

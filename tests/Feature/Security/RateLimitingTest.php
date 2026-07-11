<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Models\AuditLog;
use App\Events\SecurityAlert;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear rate limiter cache before each test
        Cache::flush();
        Carbon::setTestNow(Carbon::now());
        Event::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_auth_login_is_rate_limited()
    {
        $url = route('user.login');
        
        // 5 requests allowed per minute
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get($url);
            $this->assertNotEquals(429, $response->status());
        }

        // 6th request should be rate limited
        $response = $this->get($url);
        $response->assertStatus(429);
    }

    public function test_seller_login_is_rate_limited()
    {
        $url = route('seller.login');
        
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get($url);
            $this->assertNotEquals(429, $response->status());
        }

        $response = $this->get($url);
        $response->assertStatus(429);
    }

    public function test_password_reset_is_rate_limited()
    {
        $url = route('password.update.email');
        
        // 3 requests allowed per minute
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post($url, ['email' => 'test@example.com']);
            $this->assertNotEquals(429, $response->status());
        }

        // 4th request should be rate limited
        $response = $this->post($url, ['email' => 'test@example.com']);
        $response->assertStatus(429);
    }

    public function test_register_is_rate_limited()
    {
        $url = route('customer-reg.verification_code_send');
        
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post($url, ['email' => 'test@example.com', 'phone' => '1234567890']);
            $this->assertNotEquals(429, $response->status());
        }

        $response = $this->post($url, ['email' => 'test@example.com', 'phone' => '1234567890']);
        $response->assertStatus(429);
    }

    public function test_checkout_submit_is_rate_limited()
    {
        $url = route('checkout.store_shipping_infostore');
        
        // 10 requests allowed
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post($url);
            $this->assertNotEquals(429, $response->status());
        }

        // 11th request should be rate limited
        $response = $this->post($url);
        $response->assertStatus(429);
    }

    public function test_express_buy_is_rate_limited()
    {
        $url = route('express.buy', ['product_id' => 1]);
        $user = \App\Models\User::factory()->create();
        
        // 3 requests allowed
        for ($i = 0; $i < 3; $i++) {
            $response = $this->actingAs($user)->post($url);
            $this->assertNotEquals(429, $response->status());
        }

        // 4th request should be rate limited
        $response = $this->actingAs($user)->post($url);
        $response->assertStatus(429);
    }

    public function test_search_is_rate_limited()
    {
        $url = route('search');
        
        // 60 requests allowed
        for ($i = 0; $i < 60; $i++) {
            $response = $this->get($url);
            $this->assertNotEquals(429, $response->status());
        }

        // 61st request should be rate limited
        $response = $this->get($url);
        $response->assertStatus(429);
    }

    public function test_blog_subscribe_and_contact_form_are_rate_limited()
    {
        $blogUrl = route('blog.subscribe');
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post($blogUrl);
            $this->assertNotEquals(429, $response->status());
        }
        $response = $this->post($blogUrl);
        $response->assertStatus(429);

        $contactUrl = route('contact');
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post($contactUrl);
            $this->assertNotEquals(429, $response->status());
        }
        $response = $this->post($contactUrl);
        $response->assertStatus(429);
    }

    public function test_cmi_callback_is_rate_limited_but_allows_normal_volume()
    {
        $url = route('cmi.callback');
        Http::fake();
        
        for ($i = 0; $i < 30; $i++) {
            $response = $this->post($url);
            $this->assertNotEquals(429, $response->status());
        }

        $response = $this->post($url);
        $response->assertStatus(429);
    }

    public function test_onessta_webhook_is_rate_limited_but_allows_normal_volume()
    {
        $url = route('onessta.webhook');
        Http::fake();
        
        for ($i = 0; $i < 30; $i++) {
            $response = $this->post($url);
            $this->assertNotEquals(429, $response->status());
        }

        $response = $this->post($url);
        $response->assertStatus(429);
    }

    public function test_general_browsing_is_not_rate_limited()
    {
        for ($i = 0; $i < 70; $i++) {
            $response = $this->get('/');
            $this->assertNotEquals(429, $response->status());
        }
    }

    public function test_global_429_handler_suppresses_multiple_audit_logs()
    {
        $url = route('user.login');
        
        // Trigger 429 multiple times (limit is 5, we do 10 to trigger 5 429s)
        for ($i = 0; $i < 10; $i++) {
            $this->get($url);
        }

        // Only 1 audit log should be created due to cache suppression
        $this->assertEquals(1, AuditLog::where('action_type', 'RATE_LIMIT_EXCEEDED')->count());
    }

    public function test_global_429_handler_suppresses_security_alerts()
    {
        $url = route('user.login');
        
        for ($i = 0; $i < 10; $i++) {
            $this->get($url);
        }

        Event::assertDispatched(SecurityAlert::class, 1);
    }
}

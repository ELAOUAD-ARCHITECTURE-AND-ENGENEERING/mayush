<?php

namespace Tests\Unit\Rules;

use App\Rules\Turnstile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    /** @test */
    public function it_passes_in_local_environment()
    {
        // Mock app()->isLocal() to return true
        $this->app->instance('env', 'local');

        $rule = new Turnstile();
        
        $this->assertTrue($rule->passes('cf-turnstile-response', ''));
        $this->assertTrue($rule->passes('cf-turnstile-response', 'any-value'));
    }

    /** @test */
    public function it_passes_on_localhost_ip()
    {
        // Ensure environment is NOT local for this test
        $this->app->instance('env', 'production');

        // Mock request IP
        $this->mockRequestIp('127.0.0.1');

        $rule = new Turnstile();
        
        $this->assertTrue($rule->passes('cf-turnstile-response', ''));
    }

    /** @test */
    public function it_passes_on_ipv6_localhost_ip()
    {
        $this->app->instance('env', 'production');
        $this->mockRequestIp('::1');

        $rule = new Turnstile();
        
        $this->assertTrue($rule->passes('cf-turnstile-response', ''));
    }

    /** @test */
    public function it_fails_on_empty_value_in_production()
    {
        $this->app->instance('env', 'production');
        $this->mockRequestIp('1.2.3.4');

        $rule = new Turnstile();
        
        $this->assertFalse($rule->passes('cf-turnstile-response', ''));
        $this->assertFalse($rule->passes('cf-turnstile-response', null));
    }

    /** @test */
    public function it_calls_cloudflare_api_in_production()
    {
        $this->app->instance('env', 'production');
        $this->mockRequestIp('1.2.3.4');

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true], 200),
        ]);

        $rule = new Turnstile();
        
        $this->assertTrue($rule->passes('cf-turnstile-response', 'valid-token'));

        Http::assertSent(function ($request) {
            return $request->url() == 'https://challenges.cloudflare.com/turnstile/v0/siteverify' &&
                   $request['response'] == 'valid-token';
        });
    }

    protected function mockRequestIp($ip)
    {
        $this->instance('request', request()->duplicate([], [], [], [], [], ['REMOTE_ADDR' => $ip]));
    }
}

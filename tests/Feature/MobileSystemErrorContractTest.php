<?php

namespace Tests\Feature;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use Throwable;

class MobileSystemErrorContractTest extends TestCase
{
    use RefreshDatabase;

    private function render(Throwable $exception): TestResponse
    {
        $request = Request::create('/api/v2/system-error-contract', 'GET');
        $request->headers->set('Accept', 'application/json');

        return TestResponse::fromBaseResponse(app(\App\Exceptions\Handler::class)->render($request, $exception));
    }

    public function test_session_and_access_errors_have_stable_codes(): void
    {
        $this->render(new AuthenticationException())
            ->assertUnauthorized()
            ->assertJsonPath('code', 'SESSION_EXPIRED')
            ->assertJsonMissingPath('retry_after_seconds');

        $this->render(new HttpException(403))
            ->assertForbidden()
            ->assertJsonPath('code', 'ACCESS_DENIED')
            ->assertJsonMissingPath('retry_after_seconds');
    }

    public function test_retry_delay_is_exposed_only_for_rate_limits(): void
    {
        $this->render(new HttpException(429, '', null, ['Retry-After' => '17']))
            ->assertStatus(429)
            ->assertJsonPath('code', 'TOO_MANY_ATTEMPTS')
            ->assertJsonPath('retry_after_seconds', 17);
    }

    public function test_500_and_503_have_distinct_system_state_codes(): void
    {
        $this->render(new HttpException(500))
            ->assertStatus(500)
            ->assertJsonPath('code', 'SERVER_ERROR');

        $this->render(new HttpException(503))
            ->assertStatus(503)
            ->assertJsonPath('code', 'SERVER_UNAVAILABLE');
    }
}

<?php

namespace Tests\Feature\Payment;

use App\Http\Controllers\Payment\CybersourceController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CybersourceLoggingTest extends TestCase
{
    public function test_cybersource_logs_only_allowlisted_payment_context(): void
    {
        $controller = app(CybersourceController::class);
        $expectedContext = [
            'user_id' => 42,
            'combined_order_id' => 815,
            'payment_type' => 'cart_payment',
        ];

        Log::spy();

        foreach ([
            'pay' => 'CyberSource payment initiated',
            'process' => 'CyberSource payment process callback',
            'callback' => 'CyberSource payment callback',
            'webhook' => 'CyberSource payment webhook',
        ] as $method => $message) {
            $response = $controller->{$method}($this->sensitivePaymentRequest());

            $this->assertSame(200, $response->getStatusCode());

            Log::shouldHaveReceived('info')
                ->with($message, $expectedContext)
                ->once();
        }
    }

    public function test_cybersource_callback_logging_does_not_require_a_session(): void
    {
        Log::spy();

        $request = Request::create('/api/v2/cyber-source/payment/webhook', 'POST', [
            '_token' => 'csrf-value-must-never-be-logged',
            'password_hash_web' => 'password-hash-must-never-be-logged',
            'payment_type' => 'invalid payment type with spaces',
        ]);

        $response = app(CybersourceController::class)->webhook($request);

        $this->assertSame(200, $response->getStatusCode());
        Log::shouldHaveReceived('info')
            ->once()
            ->with('CyberSource payment webhook', [
                'user_id' => null,
                'combined_order_id' => null,
                'payment_type' => 'cybersource',
            ]);
    }

    private function sensitivePaymentRequest(): Request
    {
        $request = Request::create('/api/v2/cyber-source/payment/pay', 'POST', [
            'combined_order_id' => '815',
            'payment_type' => 'cart_payment',
            '_token' => 'csrf-value-must-never-be-logged',
            'password_hash_web' => 'password-hash-must-never-be-logged',
            'card_number' => '4111111111111111',
            'gateway_token' => 'gateway-token-must-never-be-logged',
        ]);

        $session = app('session.store');
        $session->start();
        $session->put([
            'password_hash_web' => 'session-password-hash-must-never-be-logged',
            '_token' => 'session-csrf-value-must-never-be-logged',
            'gateway_token' => 'session-gateway-token-must-never-be-logged',
        ]);
        $request->setLaravelSession($session);

        $user = new User();
        $user->id = 42;
        $request->setUserResolver(static fn (): User => $user);

        return $request;
    }
}

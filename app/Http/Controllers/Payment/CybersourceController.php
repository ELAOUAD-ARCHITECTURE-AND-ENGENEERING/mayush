<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CybersourceController extends Controller
{
    /**
     * Process CyberSource payment
     */
    public function pay(Request $request)
    {
        $this->logPaymentEvent('CyberSource payment initiated', $request);
        
        // CyberSource payment implementation would go here
        return response()->json([
            'status' => 'success',
            'message' => 'CyberSource payment processed',
            'payment_method' => 'cybersource'
        ]);
    }

    /**
     * Process CyberSource payment callback
     */
    public function process(Request $request)
    {
        $this->logPaymentEvent('CyberSource payment process callback', $request);
        
        // Process payment callback
        return response()->json([
            'status' => 'success',
            'message' => 'CyberSource payment callback processed'
        ]);
    }

    /**
     * Handle CyberSource payment callback
     */
    public function callback(Request $request)
    {
        $this->logPaymentEvent('CyberSource payment callback', $request);
        
        // Handle payment callback
        return response()->json([
            'status' => 'success',
            'message' => 'CyberSource payment callback handled'
        ]);
    }

    /**
     * Handle CyberSource payment webhook
     */
    public function webhook(Request $request)
    {
        $this->logPaymentEvent('CyberSource payment webhook', $request);
        
        // Handle payment webhook
        return response()->json([
            'status' => 'success',
            'message' => 'CyberSource payment webhook processed'
        ]);
    }

    private function logPaymentEvent(string $message, Request $request): void
    {
        Log::info($message, [
            'user_id' => $this->positiveIntegerOrNull($request->user()?->getAuthIdentifier()),
            'combined_order_id' => $this->positiveIntegerOrNull(
                $request->input('combined_order_id') ?? $this->sessionValue($request, 'combined_order_id')
            ),
            'payment_type' => $this->paymentType($request),
        ]);
    }

    private function positiveIntegerOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/', $value)) {
            return null;
        }

        return (int) $value;
    }

    private function paymentType(Request $request): string
    {
        $paymentType = $request->input('payment_type')
            ?? $request->input('payment_method')
            ?? $this->sessionValue($request, 'payment_type');

        if (is_string($paymentType) && preg_match('/^[a-z0-9_-]{1,64}$/i', $paymentType)) {
            return strtolower($paymentType);
        }

        return 'cybersource';
    }

    private function sessionValue(Request $request, string $key): mixed
    {
        return $request->hasSession() ? $request->session()->get($key) : null;
    }
}

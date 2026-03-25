<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Payment\CybersourceController as PaymentCybersourceController;
use Illuminate\Http\Request;

class CybersourceController extends Controller
{
    /**
     * Process CyberSource payment
     */
    public function pay(Request $request)
    {
        // Delegate to the payment controller
        $paymentController = new PaymentCybersourceController();
        return $paymentController->pay($request);
    }

    /**
     * Process CyberSource payment callback
     */
    public function process(Request $request)
    {
        // Delegate to the payment controller
        $paymentController = new PaymentCybersourceController();
        return $paymentController->process($request);
    }

    /**
     * Handle CyberSource payment callback
     */
    public function callback(Request $request)
    {
        // Delegate to the payment controller
        $paymentController = new PaymentCybersourceController();
        return $paymentController->callback($request);
    }

    /**
     * Handle CyberSource payment webhook
     */
    public function webhook(Request $request)
    {
        // Delegate to the payment controller
        $paymentController = new PaymentCybersourceController();
        return $paymentController->webhook($request);
    }
}
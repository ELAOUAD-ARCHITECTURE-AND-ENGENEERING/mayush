<?php

namespace App\Contracts;

use Illuminate\Http\Request;

/**
 * Interface PaymentGatewayInterface
 * 
 * Defines the standard contract for all future secure payment gateway integrations.
 * Ensures consistent handling of payment initiation, success callbacks, and failure routing
 * with built-in requirements for server-side verification and idempotency.
 */
interface PaymentGatewayInterface
{
    /**
     * Initiate the payment process.
     * Generates necessary payload, logs intent, and redirects to the gateway.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function pay(Request $request);

    /**
     * Handle the server-to-server webhook/callback from the gateway.
     * MUST implement cryptographic signature verification or server-side API validation.
     * MUST implement exactly-once idempotency checks.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request);

    /**
     * Handle the user redirect upon successful payment.
     * Should verify the transaction status before granting access to resources.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request);

    /**
     * Handle the user redirect upon failed or cancelled payment.
     * Should restore session state if necessary and log the failure.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fail(Request $request);
}
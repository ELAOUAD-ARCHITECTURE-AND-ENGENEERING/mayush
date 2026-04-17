<?php

namespace Mayush\Shipping\Onessta\Contracts;

use Illuminate\Http\Request;

interface WebhookHandlerInterface
{
    public function handle(Request $request): void;

    public function verifySignature(Request $request): bool;
}

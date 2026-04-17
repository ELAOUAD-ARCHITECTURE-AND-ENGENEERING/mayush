<?php

namespace Mayush\Shipping\Onessta\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Services\WebhookService;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        Log::info('ONESSTA: Webhook received', [
            'event' => $request->header('X-Webhook-Event'),
            'ip' => $request->ip(),
        ]);

        try {
            $log = $this->webhookService->handle($request);

            return response()->json([
                'status' => 'ok',
                'log_id' => $log->id,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ONESSTA: Webhook handler threw exception', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
            ], 500);
        }
    }
}

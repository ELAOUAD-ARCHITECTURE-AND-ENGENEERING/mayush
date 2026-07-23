<?php

namespace App\Http\Controllers;

use App\Models\NotificationDelivery;
use App\Services\Notifications\NotificationDeliveryRecorder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationDeliveryWebhookController extends Controller
{
    public function update(
        Request $request,
        string $channel,
        NotificationDeliveryRecorder $recorder
    ) {
        abort_unless(config('notifications_v2.enabled'), 404);

        $secret = (string) config('notifications_v2.delivery_webhook_secret');
        $provided = (string) $request->header('X-Notification-Webhook-Secret');
        abort_if($secret === '' || !hash_equals($secret, $provided), 401);

        $validated = $request->validate([
            'provider_reference' => ['required', 'string', 'max:191'],
            'state' => ['required', Rule::in(['delivered', 'failed'])],
            'failure_code' => ['nullable', 'string', 'max:100'],
        ]);

        $delivery = NotificationDelivery::where('channel', $channel)
            ->where('provider_reference', $validated['provider_reference'])
            ->firstOrFail();

        $recorder->transition(
            $delivery,
            $validated['state'],
            'provider_callback',
            $validated['failure_code'] ?? null,
            null,
            null,
            max(1, $delivery->attempt_count)
        );

        return response()->json(['state' => $delivery->refresh()->state]);
    }
}

<?php

namespace Mayush\Shipping\Onessta\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Mayush\Shipping\Onessta\Jobs\SyncCitiesJob;
use Mayush\Shipping\Onessta\Jobs\SyncPickupCitiesJob;
use Mayush\Shipping\Onessta\Jobs\PollTrackingJob;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Models\OnesstaCityMap;
use Mayush\Shipping\Onessta\Models\OnesstaWebhookLog;
use Mayush\Shipping\Onessta\Services\AuthService;

class AdminController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function index(): \Illuminate\View\View
    {
        $stats = [
            'total_shipments' => OnesstaShipment::count(),
            'active_shipments' => OnesstaShipment::undelivered()->count(),
            'delivered_shipments' => OnesstaShipment::where('status', 'DELIVERED')->count(),
            'failed_webhooks_24h' => OnesstaWebhookLog::failed()
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'cities_mapped' => OnesstaCityMap::active()->count(),
        ];

        $recentShipments = OnesstaShipment::with('order')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('onessta::admin.index', compact('stats', 'recentShipments'));
    }

    public function syncCities(Request $request): JsonResponse
    {
        $force = $request->boolean('force', false);

        SyncCitiesJob::dispatch($force);

        return response()->json([
            'status' => 'queued',
            'message' => 'City sync job has been queued',
            'force' => $force,
        ]);
    }

    public function syncPickupCities(Request $request): JsonResponse
    {
        $force = $request->boolean('force', false);

        SyncPickupCitiesJob::dispatch($force);

        return response()->json([
            'status' => 'queued',
            'message' => 'Pickup city sync job has been queued',
            'force' => $force,
        ]);
    }

    public function pollTracking(Request $request): JsonResponse
    {
        PollTrackingJob::dispatch();

        return response()->json([
            'status' => 'queued',
            'message' => 'Tracking poll job has been queued',
        ]);
    }

    public function shipments(Request $request): \Illuminate\View\View
    {
        $query = OnesstaShipment::with(['order', 'trackingEvents'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('receiver', 'like', "%{$search}%");
            });
        }

        $shipments = $query->paginate(20);

        return view('onessta::admin.shipments', compact('shipments'));
    }

    public function webhookLogs(Request $request): \Illuminate\View\View
    {
        $query = OnesstaWebhookLog::with('shipment')
            ->orderByDesc('id');

        $status = $request->input('status', 'failed');
        if ($status === 'failed') {
            $query->failed();
        } elseif ($status === 'processed') {
            $query->processed();
        }

        $range = $request->input('range', '24h');
        $since = match ($range) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => null,
        };
        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        if ($request->filled('event_type')) {
            $query->byEventType($request->string('event_type')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('event_type', 'like', "%{$search}%")
                    ->orWhere('error_message', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->appends($request->query());

        $eventTypes = OnesstaWebhookLog::query()
            ->select('event_type')
            ->whereNotNull('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        return view('onessta::admin.webhook_logs', compact('logs', 'eventTypes', 'status', 'range'));
    }

    public function webhookLogShow(Request $request, OnesstaWebhookLog $log): \Illuminate\View\View
    {
        $payload = null;
        if (is_string($log->payload)) {
            $decoded = json_decode($log->payload, true);
            $payload = json_last_error() === JSON_ERROR_NONE ? $decoded : $log->payload;
        } else {
            $payload = $log->payload;
        }

        $back = $request->input('back');

        return view('onessta::admin.webhook_log_show', compact('log', 'payload', 'back'));
    }

    public function validateCredentials(): JsonResponse
    {
        $isValid = $this->authService->validateCredentials();

        return response()->json([
            'valid' => $isValid,
            'configured' => $this->authService->isConfigured(),
        ]);
    }
}

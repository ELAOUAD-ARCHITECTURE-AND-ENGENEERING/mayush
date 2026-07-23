<?php

namespace App\Http\Controllers;

use App\Models\NotificationDelivery;
use App\Models\NotificationDevice;
use App\Models\NotificationPreference;
use App\Models\UserNotificationSetting;
use App\Events\NotificationInboxUpdated;
use App\Services\Notifications\NotificationCatalog;
use App\Services\Notifications\NotificationDeliveryRecorder;
use App\Services\Notifications\NotificationPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NotificationCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(
                config('notifications_v2.enabled')
                && Schema::hasTable('notification_events')
                && Schema::hasColumn('notifications', 'archived_at'),
                404
            );

            return $next($request);
        });
    }

    public function index(Request $request, NotificationPresenter $presenter)
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:50'],
            'severity' => ['nullable', Rule::in(['info', 'important', 'critical'])],
            'read' => ['nullable', Rule::in(['read', 'unread'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = $request->user()->notifications()
            ->whereNull('archived_at')
            ->when($validated['category'] ?? null, fn ($q, $value) => $q->where('category', $value))
            ->when($validated['severity'] ?? null, fn ($q, $value) => $q->where('severity', $value))
            ->when(($validated['read'] ?? null) === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when(($validated['read'] ?? null) === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->orderByDesc('created_at');

        $notifications = $query->cursorPaginate($validated['limit'] ?? 20);
        $unreadCount = $this->unreadCount($request);

        return response()->json([
            'data' => collect($notifications->items())
                ->map(fn ($notification) => $presenter->present($notification, $unreadCount))
                ->values(),
            'meta' => [
                'next_cursor' => optional($notifications->nextCursor())->encode(),
                'previous_cursor' => optional($notifications->previousCursor())->encode(),
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    public function summary(Request $request, NotificationPresenter $presenter)
    {
        $unreadCount = $this->unreadCount($request);
        $latest = $request->user()->notifications()
            ->whereNull('archived_at')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($notification) => $presenter->present($notification, $unreadCount));

        return response()->json([
            'unread_count' => $unreadCount,
            'latest' => $latest,
        ]);
    }

    public function read(Request $request, string $id, NotificationPresenter $presenter)
    {
        $notification = $this->ownedNotification($request, $id);
        $notification->markAsRead();
        $this->broadcastSync($request, 'read');

        return response()->json([
            'data' => $presenter->present($notification->refresh(), $this->unreadCount($request)),
        ]);
    }

    public function unread(Request $request, string $id, NotificationPresenter $presenter)
    {
        $notification = $this->ownedNotification($request, $id);
        $notification->forceFill(['read_at' => null])->save();
        $this->broadcastSync($request, 'unread');

        return response()->json([
            'data' => $presenter->present($notification->refresh(), $this->unreadCount($request)),
        ]);
    }

    public function readAll(Request $request)
    {
        $request->user()->notifications()
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
        $this->broadcastSync($request, 'read_all');

        return response()->json(['unread_count' => 0]);
    }

    public function archive(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => ['required', 'array', 'min:1', 'max:100'],
            'notification_ids.*' => ['required', 'uuid'],
        ]);

        $count = $request->user()->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->update(['archived_at' => now(), 'updated_at' => now()]);
        $this->broadcastSync($request, 'archive');

        return response()->json([
            'archived' => $count,
            'unread_count' => $this->unreadCount($request),
        ]);
    }

    public function broadcastAck(
        Request $request,
        string $id,
        NotificationDeliveryRecorder $recorder
    ) {
        $eventId = $request->user()->notifications()
            ->where(function ($query) use ($id) {
                $query->where('id', $id)->orWhere('event_id', $id);
            })
            ->value('event_id') ?: $id;

        $delivery = NotificationDelivery::where('event_id', $eventId)
            ->where('recipient_type', get_class($request->user()))
            ->where('recipient_id', $request->user()->id)
            ->where('channel', 'broadcast')
            ->firstOrFail();

        if ($delivery->state === 'sent') {
            $recorder->transition(
                $delivery,
                'delivered',
                'browser_acknowledged',
                null,
                null,
                null,
                max(1, $delivery->attempt_count)
            );
        }

        return response()->json(['state' => $delivery->refresh()->state]);
    }

    public function preferences(Request $request, NotificationCatalog $catalog)
    {
        $settings = UserNotificationSetting::firstOrCreate(['user_id' => $request->user()->id]);
        $preferences = NotificationPreference::where('user_id', $request->user()->id)
            ->get()
            ->keyBy('event_key');
        $payload = [
            'settings' => $settings,
            'events' => collect($catalog->all())->map(function ($definition, $eventKey) use ($preferences) {
                return [
                    'event_key' => $eventKey,
                    'category' => $definition['category'],
                    'severity' => $definition['severity'],
                    'title' => $definition['title'],
                    'mandatory_inbox' => (bool) $definition['mandatory_inbox'],
                    'channels' => $preferences->get($eventKey),
                    'defaults' => [
                        'in_app_enabled' => in_array('in_app', $definition['channels'], true),
                        'broadcast_enabled' => in_array('broadcast', $definition['channels'], true),
                        'email_enabled' => in_array('email', $definition['channels'], true),
                        'sms_enabled' => in_array('sms', $definition['channels'], true),
                        'push_enabled' => in_array('push', $definition['channels'], true),
                    ],
                ];
            })->values(),
        ];

        if (!$request->expectsJson() && !$request->is('api/*')) {
            return view('notifications.preferences', $payload);
        }

        return response()->json($payload);
    }

    public function updatePreferences(Request $request, NotificationCatalog $catalog)
    {
        $validated = $request->validate([
            'settings' => ['sometimes', 'array'],
            'settings.timezone' => ['sometimes', 'timezone'],
            'settings.quiet_hours_enabled' => ['sometimes', 'boolean'],
            'settings.quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'settings.quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'settings.in_app_enabled' => ['sometimes', 'boolean'],
            'settings.broadcast_enabled' => ['sometimes', 'boolean'],
            'settings.email_enabled' => ['sometimes', 'boolean'],
            'settings.sms_enabled' => ['sometimes', 'boolean'],
            'settings.push_enabled' => ['sometimes', 'boolean'],
            'preferences' => ['sometimes', 'array'],
            'preferences.*.event_key' => ['required', 'string'],
            'preferences.*.in_app_enabled' => ['sometimes', 'boolean'],
            'preferences.*.broadcast_enabled' => ['sometimes', 'boolean'],
            'preferences.*.email_enabled' => ['sometimes', 'boolean'],
            'preferences.*.sms_enabled' => ['sometimes', 'boolean'],
            'preferences.*.push_enabled' => ['sometimes', 'boolean'],
        ]);

        foreach ($validated['preferences'] ?? [] as $preference) {
            $definition = $catalog->get($preference['event_key']);
            if ($definition['mandatory_inbox'] && array_key_exists('in_app_enabled', $preference)
                && !$preference['in_app_enabled']) {
                return response()->json([
                    'message' => 'Critical in-app notifications cannot be disabled.',
                    'event_key' => $preference['event_key'],
                ], 422);
            }
        }

        DB::transaction(function () use ($request, $validated, $catalog) {
            if (!empty($validated['settings'])) {
                UserNotificationSetting::updateOrCreate(
                    ['user_id' => $request->user()->id],
                    $validated['settings']
                );
            }

            foreach ($validated['preferences'] ?? [] as $preference) {
                $definition = $catalog->get($preference['event_key']);
                NotificationPreference::updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'event_key' => $preference['event_key'],
                    ],
                    array_merge($preference, [
                        'notification_type_id' => DB::table('notification_types')
                            ->where('type', $definition['type'])
                            ->value('id'),
                        'in_app_enabled' => $definition['mandatory_inbox']
                            ? true
                            : ($preference['in_app_enabled'] ?? true),
                    ])
                );
            }
        });

        return $this->preferences($request, $catalog);
    }

    public function registerDevice(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', Rule::in(['web', 'android', 'ios', 'unknown'])],
        ]);
        $hash = hash('sha256', $validated['token']);

        $device = NotificationDevice::firstOrNew(['token_hash' => $hash]);
        if (!$device->exists) {
            $device->id = (string) Str::uuid();
        }
        $device->forceFill([
            'user_id' => $request->user()->id,
            'token' => $validated['token'],
            'platform' => $validated['platform'],
            'last_seen_at' => now(),
            'revoked_at' => null,
        ])->save();

        return response()->json(['id' => $device->id], 201);
    }

    public function revokeDevice(Request $request, string $id)
    {
        $device = NotificationDevice::where('user_id', $request->user()->id)->findOrFail($id);
        $device->forceFill(['revoked_at' => now()])->save();

        return response()->noContent();
    }

    private function ownedNotification(Request $request, string $id)
    {
        return $request->user()->notifications()
            ->whereNull('archived_at')
            ->where('id', $id)
            ->firstOrFail();
    }

    private function unreadCount(Request $request): int
    {
        return $request->user()->notifications()
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->count();
    }

    private function broadcastSync(Request $request, string $change): void
    {
        if (!config('notifications_v2.broadcasting_enabled')) {
            return;
        }

        $settings = UserNotificationSetting::where('user_id', $request->user()->id)->first();
        if ($settings && !$settings->broadcast_enabled) {
            return;
        }

        try {
            broadcast(new NotificationInboxUpdated($request->user()->id, [
                'schema_version' => 1,
                'type' => 'inbox_sync',
                'change' => $change,
                'unread_count' => $this->unreadCount($request),
            ]));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}

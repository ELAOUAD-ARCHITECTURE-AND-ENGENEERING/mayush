<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemStatusController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'in:android,ios,web'],
            'app_version' => ['nullable', 'regex:/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/'],
            'runtime_version' => ['nullable', 'string', 'max:100'],
            'channel' => ['nullable', 'string', 'max:50'],
            'locale' => ['nullable', 'in:fr,ar'],
        ]);

        $locale = $validated['locale'] ?? $request->header('App-Language', app()->getLocale());
        $locale = in_array($locale, ['fr', 'ar'], true) ? $locale : 'fr';
        $platform = $validated['platform'];
        $currentVersion = $validated['app_version'] ?? '0.0.0';
        $runtimeVersion = $validated['runtime_version'] ?? null;

        $latestVersion = (string) config('mobile_system.update.latest_version', '1.0.0');
        $minimumVersion = (string) config('mobile_system.update.minimum_version', '1.0.0');
        $latestRuntimeVersion = (string) config('mobile_system.update.latest_runtime_version', $latestVersion);

        $updateStatus = 'none';
        $delivery = null;
        if (version_compare($currentVersion, $minimumVersion, '<')) {
            $updateStatus = 'mandatory';
            $delivery = 'store';
        } elseif (version_compare($currentVersion, $latestVersion, '<')) {
            $updateStatus = 'optional';
            $delivery = $runtimeVersion && hash_equals($latestRuntimeVersion, $runtimeVersion) ? 'ota' : 'store';
        }

        $now = CarbonImmutable::now();
        $startsAt = $this->parseDate(config('mobile_system.maintenance.starts_at'));
        $endsAt = $this->parseDate(config('mobile_system.maintenance.ends_at'));
        $configuredActive = (bool) config('mobile_system.maintenance.active', false);
        $insideWindow = $startsAt && $startsAt->lessThanOrEqualTo($now) && (!$endsAt || $endsAt->isFuture());
        $maintenanceActive = $configuredActive || $insideWindow;
        $maintenanceScheduled = !$maintenanceActive && $startsAt && $startsAt->isFuture();

        return response()->json([
            'result' => true,
            'data' => [
                'checked_at' => $now->toIso8601String(),
                'update' => [
                    'status' => $updateStatus,
                    'delivery' => $delivery,
                    'current_version' => $currentVersion,
                    'latest_version' => $latestVersion,
                    'minimum_version' => $minimumVersion,
                    'runtime_version' => $runtimeVersion,
                    'latest_runtime_version' => $latestRuntimeVersion,
                    'channel' => $validated['channel'] ?? null,
                    'published_at' => config('mobile_system.update.published_at'),
                    'store_url' => config("mobile_system.update.store_urls.{$platform}"),
                    'release_notes' => config("mobile_system.update.release_notes.{$locale}", []),
                ],
                'maintenance' => [
                    'status' => $maintenanceActive ? 'active' : ($maintenanceScheduled ? 'scheduled' : 'none'),
                    'global_block' => $maintenanceActive,
                    'title' => config("mobile_system.maintenance.title.{$locale}"),
                    'message' => config("mobile_system.maintenance.message.{$locale}"),
                    'starts_at' => $startsAt?->toIso8601String(),
                    'ends_at' => $endsAt?->toIso8601String(),
                    'support_url' => config('mobile_system.maintenance.support_url'),
                    'updated_at' => config('mobile_system.maintenance.updated_at'),
                ],
                'service' => [
                    'available' => (bool) config('mobile_system.service.available', true),
                    'retry_after_seconds' => (int) config('mobile_system.service.retry_after_seconds', 60),
                ],
            ],
        ]);
    }

    private function parseDate(?string $value): ?CarbonImmutable
    {
        if (!$value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}

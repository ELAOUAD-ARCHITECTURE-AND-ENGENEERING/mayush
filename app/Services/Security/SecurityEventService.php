<?php

namespace App\Services\Security;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;

class SecurityEventService
{
    /**
     * Whether unusual-activity detection is enforced (blocks login)
     * or observe-only (logs + flags, but allows login).
     *
     * Controlled via SECURITY_UNUSUAL_ACTIVITY_ENFORCE env variable.
     * Default: false (observe-only) — enable after 30+ days of data collection.
     */
    public static function isEnforced(): bool
    {
        return filter_var(config('services.security.unusual_activity_enforce', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Log a security event and check for anomalies.
     *
     * Returns the created event. If flagged and enforcement is on,
     * the caller should block the login with UNUSUAL_ACTIVITY_VERIFICATION_REQUIRED.
     */
    public static function logLoginEvent(User $user, Request $request, string $eventType = 'login_success'): SecurityEvent
    {
        $ip = $request->ip();
        $deviceId = $request->header('X-Device-Id');
        $userAgent = $request->userAgent();

        $flagResult = self::detectAnomaly($user, $ip, $deviceId);

        return SecurityEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'ip_address' => $ip,
            'device_id' => $deviceId,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'ip_country' => null, // Populated async via queue job if GeoIP configured
            'ip_city' => null,
            'flagged' => $flagResult['flagged'],
            'flag_reason' => $flagResult['reason'],
        ]);
    }

    /**
     * Log a non-login security event (password change, device verified, etc.).
     */
    public static function logEvent(User $user, Request $request, string $eventType): SecurityEvent
    {
        return SecurityEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'ip_address' => $request->ip(),
            'device_id' => $request->header('X-Device-Id'),
            'user_agent' => $request->userAgent() ? substr($request->userAgent(), 0, 500) : null,
            'flagged' => false,
            'flag_reason' => null,
        ]);
    }

    /**
     * Detect login anomalies by comparing against the user's recent history.
     *
     * Checks (in order):
     * 1. New IP /16 subnet not seen in the last 90 days
     * 2. Login from a new device_id when user has established device history
     *
     * Returns ['flagged' => bool, 'reason' => string|null].
     */
    private static function detectAnomaly(User $user, string $ip, ?string $deviceId): array
    {
        $recentEvents = SecurityEvent::where('user_id', $user->id)
            ->where('event_type', 'login_success')
            ->where('created_at', '>=', now()->subDays(90))
            ->get();

        // No history yet — first login ever or within window, not anomalous
        if ($recentEvents->isEmpty()) {
            return ['flagged' => false, 'reason' => null];
        }

        // Check 1: IP subnet change (/16 prefix — first two octets for IPv4)
        $currentPrefix = self::ipPrefix($ip);
        if ($currentPrefix !== null) {
            $knownPrefixes = $recentEvents
                ->map(fn ($e) => self::ipPrefix($e->ip_address))
                ->filter()
                ->unique()
                ->values();

            if ($knownPrefixes->isNotEmpty() && !$knownPrefixes->contains($currentPrefix)) {
                return ['flagged' => true, 'reason' => 'new_ip_range'];
            }
        }

        // Check 2: New device ID when user has established devices
        if ($deviceId) {
            $knownDeviceIds = $recentEvents
                ->pluck('device_id')
                ->filter()
                ->unique();

            if ($knownDeviceIds->count() >= 1 && !$knownDeviceIds->contains($deviceId)) {
                // Only flag if device verification didn't already handle this
                // (device verification creates a UserDevice record)
                $deviceVerified = \App\Models\UserDevice::where('user_id', $user->id)
                    ->where('device_id', $deviceId)
                    ->whereNotNull('verified_at')
                    ->exists();

                if (!$deviceVerified) {
                    return ['flagged' => true, 'reason' => 'new_device'];
                }
            }
        }

        return ['flagged' => false, 'reason' => null];
    }

    /**
     * Extract the /16 prefix from an IPv4 address (first two octets).
     * Returns null for IPv6 or invalid addresses.
     */
    private static function ipPrefix(string $ip): ?string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return null;
        }
        $parts = explode('.', $ip);
        return $parts[0] . '.' . $parts[1];
    }
}

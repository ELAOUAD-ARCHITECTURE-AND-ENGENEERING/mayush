<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\SecurityChallenge;
use App\Models\UserDevice;
use App\Notifications\DeviceVerificationNotification;
use App\Services\Security\SecurityEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * Verify a device using the challenge code.
     * POST /api/v2/auth/verify-device
     */
    public function verifyDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'challenge_id' => 'required|integer',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all(),
            ], 422);
        }

        $challenge = SecurityChallenge::find($request->challenge_id);

        if (!$challenge || $challenge->type !== 'device_verification') {
            return response()->json([
                'result' => false,
                'message' => 'Défi de vérification introuvable.',
            ], 404);
        }

        if ($challenge->isExpired()) {
            return response()->json([
                'result' => false,
                'code' => 'CHALLENGE_EXPIRED',
                'message' => 'Le code de vérification a expiré. Veuillez en demander un nouveau.',
            ], 410);
        }

        if ($challenge->attempts >= 5) {
            return response()->json([
                'result' => false,
                'code' => 'TOO_MANY_ATTEMPTS',
                'message' => 'Trop de tentatives. Veuillez demander un nouveau code.',
            ], 429);
        }

        if (!$challenge->verifyCode($request->code)) {
            return response()->json([
                'result' => false,
                'message' => 'Code de vérification incorrect.',
                'remaining_attempts' => max(0, 5 - $challenge->attempts),
            ], 401);
        }

        // Create or update the verified device
        $device = UserDevice::updateOrCreate(
            ['user_id' => $challenge->user_id, 'device_id' => $challenge->device_id],
            [
                'verified_at' => now(),
                'last_seen_at' => now(),
                'device_name' => $request->header('User-Agent', 'Unknown Device'),
            ]
        );

        // Log device verification event
        $user = $challenge->user;
        SecurityEventService::logEvent($user, $request, 'device_verified');

        // Issue Sanctum token linked to the device
        $token = $user->createToken('API Token');
        $token->accessToken->device_id = $device->id;
        $token->accessToken->save();

        return response()->json([
            'result' => true,
            'message' => 'Appareil vérifié avec succès.',
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => uploaded_asset($user->avatar_original),
                'phone' => $user->phone,
                'email_verified' => $user->email_verified_at != null,
            ],
        ]);
    }

    /**
     * Resend the device verification code.
     * POST /api/v2/auth/resend-device-code
     */
    public function resendDeviceCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'challenge_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => $validator->errors()->all()], 422);
        }

        $oldChallenge = SecurityChallenge::find($request->challenge_id);
        if (!$oldChallenge || $oldChallenge->type !== 'device_verification') {
            return response()->json(['result' => false, 'message' => 'Défi introuvable.'], 404);
        }

        $user = $oldChallenge->user;
        ['challenge' => $newChallenge, 'code' => $code] = SecurityChallenge::createForDevice(
            $user,
            $oldChallenge->device_id,
            'device_verification'
        );

        // Send verification code
        try {
            $user->notify(new DeviceVerificationNotification($code));
        } catch (\Exception $e) {
            // Log but don't expose
        }

        return response()->json([
            'result' => true,
            'message' => 'Un nouveau code de vérification a été envoyé.',
            'challenge_id' => $newChallenge->id,
            'expires_at' => $newChallenge->expires_at->toIso8601String(),
        ]);
    }

    /**
     * List verified devices for the authenticated user.
     * GET /api/v2/auth/devices
     */
    public function listDevices(Request $request)
    {
        $devices = UserDevice::where('user_id', $request->user()->id)
            ->whereNotNull('verified_at')
            ->orderByDesc('last_seen_at')
            ->get()
            ->map(fn ($device) => [
                'id' => $device->id,
                'device_name' => $device->device_name,
                'verified_at' => $device->verified_at->toIso8601String(),
                'last_seen_at' => $device->last_seen_at?->toIso8601String(),
                'is_current' => $request->user()->currentAccessToken()->device_id === $device->id,
            ]);

        return response()->json([
            'result' => true,
            'data' => $devices,
        ]);
    }

    /**
     * Revoke a verified device and its tokens.
     * DELETE /api/v2/auth/devices/{id}
     */
    public function revokeDevice(Request $request, $id)
    {
        $device = UserDevice::where('user_id', $request->user()->id)->find($id);

        if (!$device) {
            return response()->json(['result' => false, 'message' => 'Appareil introuvable.'], 404);
        }

        // Don't allow revoking the current device
        $currentDeviceId = $request->user()->currentAccessToken()->device_id;
        if ($device->id === $currentDeviceId) {
            return response()->json([
                'result' => false,
                'message' => 'Vous ne pouvez pas révoquer l\'appareil actuellement connecté.',
            ], 422);
        }

        // Delete tokens associated with this device
        $request->user()->tokens()->where('device_id', $device->id)->delete();
        $device->delete();

        return response()->json([
            'result' => true,
            'message' => 'Appareil révoqué avec succès.',
        ]);
    }
}

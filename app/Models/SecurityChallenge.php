<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecurityChallenge extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'type',
        'code_hash',
        'method',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new challenge with a 6-digit code.
     * Returns the plain code (for sending) — only the hash is stored.
     */
    public static function createForDevice(User $user, string $deviceId, string $type = 'device_verification'): array
    {
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Expire any existing challenges of the same type for this device
        static::where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        $challenge = static::create([
            'user_id' => $user->id,
            'device_id' => $deviceId,
            'type' => $type,
            'code_hash' => Hash::make($code),
            'method' => $user->email ? 'email' : 'sms',
            'expires_at' => now()->addMinutes(10),
        ]);

        return ['challenge' => $challenge, 'code' => $code];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function verifyCode(string $code): bool
    {
        if ($this->isExpired() || $this->isVerified()) {
            return false;
        }

        if ($this->attempts >= 5) {
            return false;
        }

        $this->increment('attempts');

        if (Hash::check($code, $this->code_hash)) {
            $this->verified_at = now();
            $this->save();
            return true;
        }

        return false;
    }
}

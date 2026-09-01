<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginSecurityState extends Model
{
    protected $fillable = [
        'user_id',
        'consecutive_failures',
        'escalation_level',
        'locked_until',
        'hold_reason',
    ];

    protected $casts = [
        'locked_until' => 'datetime',
        'consecutive_failures' => 'integer',
        'escalation_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check escalation thresholds and apply lock if needed.
     * Levels: 0=none, 1=2min (at 5 fails), 2=15min (at 8), 3=24h (at 11), 4=hold (at 14)
     */
    public function applyEscalation(): void
    {
        $thresholds = [
            ['failures' => 5,  'level' => 1, 'minutes' => 2],
            ['failures' => 8,  'level' => 2, 'minutes' => 15],
            ['failures' => 11, 'level' => 3, 'minutes' => 1440], // 24 hours
            ['failures' => 14, 'level' => 4, 'minutes' => null],  // indefinite hold
        ];

        foreach (array_reverse($thresholds) as $threshold) {
            if ($this->consecutive_failures >= $threshold['failures'] && $this->escalation_level < $threshold['level']) {
                $this->escalation_level = $threshold['level'];

                if ($threshold['minutes'] !== null) {
                    $this->locked_until = now()->addMinutes($threshold['minutes']);
                } else {
                    // Indefinite security hold — set on user record
                    $this->user->security_hold_at = now();
                    $this->user->save();
                    $this->hold_reason = 'Dépassement du nombre maximal de tentatives de connexion';
                }

                break;
            }
        }
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function remainingLockSeconds(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }

        return (int) now()->diffInSeconds($this->locked_until, false);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentToken extends Model
{
    use HasFactory;

    protected $hidden = [
        'token',
    ];

    protected $fillable = [
        'user_id',
        'gateway',
        'token',
        'card_last_four',
        'card_brand',
        'card_fingerprint',
        'card_expiry_month',
        'card_expiry_year',
        'is_default',
        'is_active',
        'last_used_at'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'token' => 'encrypted',
        'card_last_four' => 'encrypted',
        'card_expiry_month' => 'integer',
        'card_expiry_year' => 'integer',
    ];

    /**
     * Maximum number of vault tokens allowed per user per gateway.
     */
    const MAX_TOKENS_PER_USER = 5;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: only non-expired tokens (or tokens with no expiry data).
     */
    public function scopeNonExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('card_expiry_year')
              ->orWhere(function ($inner) {
                  $now = now();
                  $inner->where('card_expiry_year', '>', $now->year)
                        ->orWhere(function ($yq) use ($now) {
                            $yq->where('card_expiry_year', $now->year)
                               ->where('card_expiry_month', '>=', $now->month);
                        });
              });
        });
    }

    /**
     * Check if this token's card has expired.
     */
    public function isExpired(): bool
    {
        if (!$this->card_expiry_month || !$this->card_expiry_year) {
            return false; // No expiry data — assume valid
        }

        $now = now();
        if ($this->card_expiry_year < $now->year) {
            return true;
        }
        if ($this->card_expiry_year == $now->year && $this->card_expiry_month < $now->month) {
            return true;
        }

        return false;
    }

    /**
     * Bulk-deactivate all expired tokens across the system.
     * Returns the count of tokens deactivated.
     */
    public static function pruneExpired(): int
    {
        $now = now();

        return static::where('is_active', true)
            ->whereNotNull('card_expiry_year')
            ->whereNotNull('card_expiry_month')
            ->where(function ($q) use ($now) {
                $q->where('card_expiry_year', '<', $now->year)
                  ->orWhere(function ($inner) use ($now) {
                      $inner->where('card_expiry_year', $now->year)
                            ->where('card_expiry_month', '<', $now->month);
                  });
            })
            ->update(['is_active' => false]);
    }

    /**
     * Get a masked label for the UI (e.g. "Visa •••• 4242")
     */
    public function maskedLabel()
    {
        $brand = $this->card_brand ? ucfirst(strtolower($this->card_brand)) : 'Card';
        $last4 = $this->card_last_four ?? '****';
        $expiry = ($this->card_expiry_month && $this->card_expiry_year)
            ? ' · ' . str_pad($this->card_expiry_month, 2, '0', STR_PAD_LEFT) . '/' . substr($this->card_expiry_year, -2)
            : '';
        return "{$brand} •••• {$last4}{$expiry}";
    }
}

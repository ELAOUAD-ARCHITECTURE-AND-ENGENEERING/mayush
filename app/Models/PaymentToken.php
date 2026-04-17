<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gateway',
        'token',
        'card_last_four',
        'card_brand',
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
        'card_last_four' => 'encrypted'
    ];

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
     * Get a masked label for the UI (e.g. "Visa •••• 4242")
     */
    public function maskedLabel()
    {
        $brand = $this->card_brand ? ucfirst(strtolower($this->card_brand)) : 'Card';
        $last4 = $this->card_last_four ?? '****';
        return "{$brand} •••• {$last4}";
    }
}

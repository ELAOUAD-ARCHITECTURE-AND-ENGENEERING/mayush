<?php

namespace Mayush\Shipping\Onessta\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OnesstaShipment extends Model
{
    use HasFactory;

    protected $table = 'onessta_shipments';

    protected $fillable = [
        'order_id',
        'external_id',
        'code',
        'receiver',
        'phone',
        'address',
        'city_id',
        'city_name',
        'remote_city_id',
        'pickup_city_id',
        'pickup_city_name',
        'price',
        'sku',
        'note',
        'product_nature',
        'can_open',
        'replace',
        'is_cod',
        'payment_situation',
        'status',
        'status_second',
        'situation',
        'last_status_comment',
        'reported_date',
        'raw_request',
        'raw_response',
        'created_at_remote',
        'updated_at_remote',
        'synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'can_open' => 'boolean',
        'replace' => 'boolean',
        'is_cod' => 'boolean',
        'raw_request' => 'array',
        'raw_response' => 'array',
        'reported_date' => 'datetime',
        'created_at_remote' => 'datetime',
        'updated_at_remote' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(OnesstaTrackingEvent::class, 'onessta_shipment_id');
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(OnesstaWebhookLog::class, 'onessta_shipment_id');
    }

    public function scopeUndelivered($query)
    {
        return $query->whereNotIn('status', ['DELIVERED', 'RETURNED', 'CANCELLED']);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function getDeliveryStatusAttribute(): string
    {
        return match ($this->status) {
            'WAITING_PICKUP' => 'pending',
            'PICKED_UP' => 'picked_up',
            'SENT' => 'on_the_way',
            'RECEIVED' => 'processing',
            'DISTRIBUTION' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RETURNING' => 'return_requested',
            'RETURNED' => 'refunded',
            'CANCELLED' => 'cancelled',
            default => 'pending',
        };
    }
}

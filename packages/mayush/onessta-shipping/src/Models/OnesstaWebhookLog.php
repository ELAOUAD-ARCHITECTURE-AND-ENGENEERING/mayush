<?php

namespace Mayush\Shipping\Onessta\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnesstaWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'onessta_webhook_logs';

    protected $fillable = [
        'event_type',
        'header_api_key',
        'header_signature',
        'header_event',
        'payload',
        'signature_valid',
        'processed',
        'error_message',
        'onessta_shipment_id',
        'processed_at',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OnesstaShipment::class, 'onessta_shipment_id');
    }

    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('processed', false)->whereNotNull('error_message');
    }

    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'processed' => false,
            'error_message' => $errorMessage,
        ]);
    }
}

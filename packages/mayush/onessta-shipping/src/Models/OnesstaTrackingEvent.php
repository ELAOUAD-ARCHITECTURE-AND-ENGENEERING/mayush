<?php

namespace Mayush\Shipping\Onessta\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnesstaTrackingEvent extends Model
{
    use HasFactory;

    protected $table = 'onessta_tracking_events';

    protected $fillable = [
        'onessta_shipment_id',
        'status',
        'name',
        'created_at_remote',
        'new_date',
        'raw_payload',
    ];

    protected $casts = [
        'created_at_remote' => 'datetime',
        'new_date' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OnesstaShipment::class, 'onessta_shipment_id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}

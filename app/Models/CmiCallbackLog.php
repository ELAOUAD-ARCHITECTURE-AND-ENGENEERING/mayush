<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmiCallbackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway', 'payment_attempt_id', 'combined_order_id', 'order_id',
        'merchant_reference', 'gateway_reference', 'payload_hash',
        'signature_valid', 'is_duplicate', 'processing_status',
        'raw_payload', 'normalized_payload', 'ip_address', 'user_agent',
        'received_at', 'processed_at', 'error_message'
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'is_duplicate' => 'boolean',
        'raw_payload' => 'array',
        'normalized_payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function paymentAttempt()
    {
        return $this->belongsTo(PaymentAttempt::class);
    }
}

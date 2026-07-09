<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'combined_order_id', 'order_id', 'payment_method', 'gateway',
        'gateway_reference', 'merchant_reference', 'amount', 'currency', 'status',
        'request_payload_hash', 'response_payload_hash', 'initiated_at',
        'completed_at', 'failed_at', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function combinedOrder()
    {
        return $this->belongsTo(CombinedOrder::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

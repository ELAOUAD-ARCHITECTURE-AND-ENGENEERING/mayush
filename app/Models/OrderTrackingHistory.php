<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTrackingHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expected_delivery_date' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

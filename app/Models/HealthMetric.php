<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'source', 'value', 'unit', 'message', 'context', 'created_at'
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public $timestamps = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'user_id', 'ip_address', 'user_agent', 'url', 'method',
        'referrer', 'country_code', 'city', 'is_entry', 'is_exit',
        'time_spent', 'click_paths', 'utm'
    ];

    protected $casts = [
        'click_paths' => 'array',
        'utm' => 'array',
        'is_entry' => 'boolean',
        'is_exit' => 'boolean',
    ];
}

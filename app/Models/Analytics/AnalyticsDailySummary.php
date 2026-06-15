<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailySummary extends Model
{
    protected $table = 'analytics_daily_summaries';

    protected $fillable = [
        'metric_type',
        'dimension',
        'value',
        'date',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'date' => 'date',
    ];
}

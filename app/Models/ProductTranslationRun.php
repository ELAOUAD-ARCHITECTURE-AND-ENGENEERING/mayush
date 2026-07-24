<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTranslationRun extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_progress_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ProductTranslationRunItem::class, 'run_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['queued', 'running', 'paused'], true);
    }
}

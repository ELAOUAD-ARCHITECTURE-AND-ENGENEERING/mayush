<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageOptimizationState extends Model
{
    protected $fillable = [
        'upload_id',
        'source_kind',
        'disk',
        'source_path',
        'source_fingerprint',
        'recipe_version',
        'status',
        'last_error',
        'last_checked_at',
        'optimized_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'optimized_at' => 'datetime',
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTranslationRunItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'missing_fields' => 'array',
        'source_missing_fields' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function run()
    {
        return $this->belongsTo(ProductTranslationRun::class, 'run_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

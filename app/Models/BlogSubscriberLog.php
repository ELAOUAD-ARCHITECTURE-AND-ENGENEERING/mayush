<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogSubscriberLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subscribed_at' => 'datetime',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}

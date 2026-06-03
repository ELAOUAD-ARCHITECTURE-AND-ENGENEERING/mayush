<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogVersion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspirationHotspot extends Model
{
    protected $fillable = [
        'inspiration_id', 'inspiration_item_id', 'x', 'y', 'display_order',
    ];

    protected $casts = [
        'x' => 'decimal:4',
        'y' => 'decimal:4',
    ];

    public function inspiration()
    {
        return $this->belongsTo(Inspiration::class);
    }

    public function item()
    {
        return $this->belongsTo(InspirationItem::class, 'inspiration_item_id');
    }
}

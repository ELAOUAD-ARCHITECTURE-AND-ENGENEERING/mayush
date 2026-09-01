<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspirationItem extends Model
{
    protected $fillable = [
        'inspiration_id', 'product_id', 'display_order',
        'is_visible', 'is_featured', 'custom_title_fr', 'custom_title_ar',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function inspiration()
    {
        return $this->belongsTo(Inspiration::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function hotspot()
    {
        return $this->hasOne(InspirationHotspot::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerTextVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'lang',
        'value',
        'changed_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

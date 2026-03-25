<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'tier',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $dates = ['start_date', 'end_date'];

    public function product()
    {
        return $this->belongsTo(CustomerProduct::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

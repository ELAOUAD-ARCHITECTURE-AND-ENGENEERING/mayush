<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Wallet extends Model
{
    use PreventDemoModeChanges;

    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_details',
        'payment_reference',
        'approval',
        'offline_payment',
        'reciept',
    ];

    protected $casts = [
        'amount' => 'float',
        'approval' => 'boolean',
        'offline_payment' => 'boolean',
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }
}

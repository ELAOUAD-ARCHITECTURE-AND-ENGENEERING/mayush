<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventDemoModeChanges;

class CombinedOrder extends Model
{
    use PreventDemoModeChanges, HasFactory;
    
    protected $fillable = ['user_id', 'shipping_address', 'grand_total'];

    public function orders(){
    	return $this->hasMany(Order::class);
    }

    public function user(){
    	return $this->belongsTo(User::class);
    }
}

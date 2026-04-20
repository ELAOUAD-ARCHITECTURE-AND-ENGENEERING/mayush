<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDetail extends Model
{
    use PreventDemoModeChanges, HasFactory;
    
    protected $fillable = [
        'order_id', 'product_id', 'seller_id', 'variant', 'payment_status', 'delivery_status', 
        'shipping_type', 'pickup_point_id', 'product_referral_code', 'shipping_cost', 'quantity', 'price', 'tax'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function refund_request()
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }
}

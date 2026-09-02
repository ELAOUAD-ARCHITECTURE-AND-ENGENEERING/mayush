<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use PreventDemoModeChanges, HasFactory, Auditable;

    protected $fillable = [
        'combined_order_id', 'user_id', 'seller_id', 'shipping_address', 'billing_address',
        'payment_type', 'payment_status', 'grand_total', 'code', 'invoice_number', 'order_note', 'date', 'is_confirmed'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
    ];
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function orderTrackingHistories()
    {
        return $this->hasMany(OrderTrackingHistory::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }

    public function commissionHistory()
    {
        return $this->hasOne(CommissionHistory::class);
    }
}

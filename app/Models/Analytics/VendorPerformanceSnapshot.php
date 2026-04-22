<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VendorPerformanceSnapshot extends Model
{
    protected $table = 'vendor_performance_snapshots';

    protected $fillable = [
        'seller_id',
        'total_revenue',
        'dispute_count',
        'orders_count',
        'avg_rating',
        'snapshot_date',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'snapshot_date' => 'date',
    ];

    /**
     * The vendor (seller) user associated with this snapshot.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}

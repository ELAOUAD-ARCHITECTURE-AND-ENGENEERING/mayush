<?php

namespace Mayush\Shipping\Onessta\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnesstaCityMap extends Model
{
    use HasFactory;

    protected $table = 'onessta_city_maps';

    protected $fillable = [
        'remote_city_id',
        'remote_city_name',
        'local_city_id',
        'local_city_name',
        'is_pickup',
        'active',
    ];

    protected $casts = [
        'is_pickup' => 'boolean',
        'active' => 'boolean',
    ];

    public function localCity(): BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class, 'local_city_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopePickup($query)
    {
        return $query->where('is_pickup', true);
    }

    public function scopeByLocalCityId($query, int $localCityId)
    {
        return $query->where('local_city_id', $localCityId);
    }

    public function scopeByRemoteCityId($query, int $remoteCityId)
    {
        return $query->where('remote_city_id', $remoteCityId);
    }
}

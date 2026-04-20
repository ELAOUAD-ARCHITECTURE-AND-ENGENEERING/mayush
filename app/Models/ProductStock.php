<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductStock extends Model
{
    use PreventDemoModeChanges, HasFactory;

    protected $fillable = ['product_id', 'variant', 'sku', 'price', 'qty', 'image', 'length', 'width', 'height', 'dimension_unit'];
    //
    public function product(){
    	return $this->belongsTo(Product::class);
    }

    public function wholesalePrices() {
        return $this->hasMany(WholesalePrice::class);
    }
}

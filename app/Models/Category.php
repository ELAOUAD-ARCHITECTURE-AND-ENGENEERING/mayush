<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use PreventDemoModeChanges, HasFactory;
    protected $guarded = [];

    protected $with = ['category_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $category_translation = $this->category_translations->where('lang', $lang)->first();

        if ($category_translation != null && $category_translation->$field !== null && $category_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($category_translation->$field, $lang) : $category_translation->$field;
        }

        return $category_translation != null ? $category_translation->$field : $this->$field;
    }

    public function category_translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function coverImage()
    {
        return $this->belongsTo(Upload::class, 'cover_image');
    }

    public function catIcon()
    {
        return $this->belongsTo(Upload::class, 'icon');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function preorderProducts()
    {
        return $this->belongsToMany(PreorderProduct::class, 'preorder_product_categories');
    }

    public function bannerImage()
    {
        return $this->belongsTo(Upload::class, 'banner');
    }

    public function classified_products()
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('categories');
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class);
    }

    public function sizeChart()
    {
        return $this->belongsTo(SizeChart::class, 'id', 'category_id');
    }

   public function sellerDiscount()
    {
        return $this->hasOne(SellerCategory::class)->where('seller_id', auth()->id());
    }

    public function sellerDiscounts()
    {
        return $this->hasMany(SellerCategory::class);
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function descendantIds($ids = [])
    {
        foreach ($this->childrenCategories as $child) {
            $ids[] = $child->id;
            $ids = $child->descendantIds($ids);
        }
        return $ids;
    }

    public function getTotalProductCountAttribute()
    {
        $categoryIds = array_merge([$this->id], $this->descendantIds());

        $directProductIds = \App\Models\Product::whereIn('category_id', $categoryIds)->pluck('id');
        $pivotProductIds = \DB::table('product_categories')->whereIn('category_id', $categoryIds)->pluck('product_id');

        return $directProductIds->merge($pivotProductIds)->unique()->count();
    }
}

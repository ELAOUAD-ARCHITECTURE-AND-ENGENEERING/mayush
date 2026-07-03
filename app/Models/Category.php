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
        $base_value = $this->$field;

        if ($category_translation != null && $category_translation->$field !== null && trim((string) $category_translation->$field) !== '') {
            $translated_value = $category_translation->$field;

            if ($this->shouldUseGlobalTranslationFallback($field, $translated_value, $base_value)) {
                return $this->translateBaseValue($base_value, $lang);
            }

            return $translated_value;
        }

        if (in_array($field, ['name', 'title'], true) && trim((string) $base_value) !== '') {
            return $this->translateBaseValue($base_value, $lang);
        }

        return $base_value;
    }

    private function shouldUseGlobalTranslationFallback(string $field, $translated_value, $base_value): bool
    {
        return in_array($field, ['name', 'title'], true)
            && trim((string) $translated_value) === trim((string) $base_value);
    }

    private function translateBaseValue($base_value, string $lang)
    {
        $translated_value = translate($base_value, $lang);

        return trim((string) $translated_value) !== '' ? $translated_value : $base_value;
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

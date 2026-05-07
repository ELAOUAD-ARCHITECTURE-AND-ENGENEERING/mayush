<?php

namespace App\Models;

use App\Models\Product;
use App\Traits\PreventDemoModeChanges;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithMapping, WithHeadings
{
    use PreventDemoModeChanges;

    public function collection()
    {
        return Product::with(['stocks', 'product_categories'])->get();
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
            'category_id',
            'multi_categories',
            'brand_id',
            'video_provider',
            'video_link',
            'tags',
            'unit_price',
            'unit',
            'slug',
            'current_stock',
            'est_shipping_days',
            'sku',
            'meta_title',
            'meta_description',
            'thumbnail_img',
            'photos',
            'discount',
            'discount_type',
            'published',
            'approved',
            'variations',
        ];
    }

    /**
    * @var Product $product
    */
    public function map($product): array
    {
        $stocks = $product->stocks;
        $qty = $stocks->sum('qty');
        $sku = $stocks->pluck('sku')->filter()->implode(',');

        return [
            $product->name,
            $product->description,
            $product->category_id,
            $product->product_categories->pluck('category_id')->unique()->implode(','),
            $product->brand_id,
            $product->video_provider,
            $product->video_link,
            $product->tags,
            $product->unit_price,
            $product->unit,
            $product->slug,
            $qty,
            $product->est_shipping_days,
            $sku,
            $product->meta_title,
            $product->meta_description,
            $product->thumbnail_img,
            $product->photos,
            $product->discount,
            $product->discount_type,
            $product->published,
            $product->approved,
            $product->variations,
        ];
    }
}

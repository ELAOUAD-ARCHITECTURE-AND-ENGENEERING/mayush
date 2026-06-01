<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ProductCollection;
use App\Services\ProductCollectionService;
use Illuminate\Http\Request;

class ProductCollectionController extends Controller
{
    public function show(Request $request, string $slug, ProductCollectionService $service)
    {
        $collection = ProductCollection::published()->where('slug', $slug)->firstOrFail();
        $sort = $request->input('sort_by', $collection->default_sort ?: 'newest');

        $products = $service->query($collection);
        $service->applyRequestFilters($products, [
            'keyword' => $request->input('keyword'),
            'brand' => $request->input('brand'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
        ]);
        $service->applySort($products, $sort);

        $products = $products->with('taxes')->paginate(24)->appends($request->query());
        $bestSellingProducts = $collection->show_best_selling
            ? $service->bestSelling($collection)
            : collect();
        $recentlyViewedProducts = $collection->show_recently_viewed
            ? $service->recentlyViewed($collection, auth()->id())
            : collect();
        $brands = Brand::whereIn('id', $service->query($collection)->whereNotNull('brand_id')->pluck('brand_id')->unique())
            ->orderBy('name')
            ->get();

        return view('frontend.product_collections.show', compact(
            'collection',
            'products',
            'bestSellingProducts',
            'recentlyViewedProducts',
            'brands',
            'sort'
        ));
    }
}

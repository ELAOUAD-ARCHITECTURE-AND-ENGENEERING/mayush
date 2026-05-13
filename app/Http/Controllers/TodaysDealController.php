<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TodaysDealController extends Controller
{
    public function __construct(private ProductService $productService)
    {
        $this->middleware(['permission:view_promotional_product'])->only('index');
        $this->middleware(['permission:add_todays_deal_products'])->only('search', 'update');
    }

    public function index()
    {
        $seller_type = '';
        $categories = Category::where('parent_id', 0)->with('childrenCategories')->get();
        $product_types = ['Todays Deal Product List'];

        return view('backend.promotion_and_offers.todays_deal.index', compact('seller_type', 'categories', 'product_types'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'all_ids' => ['required', 'array'],
            'all_ids.*' => ['integer', 'exists:products,id'],
            'checked_ids' => ['nullable', 'array'],
            'checked_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $checkedIds = collect($data['checked_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
        $allIds = collect($data['all_ids'])->map(fn ($id) => (int) $id)->all();
        $uncheckedIds = array_diff($allIds, $checkedIds);

        if ($checkedIds !== []) {
            Product::whereIn('id', $checkedIds)->update(['todays_deal' => 1, 'promotional' => 1]);
        }

        if ($uncheckedIds !== []) {
            Product::whereIn('id', $uncheckedIds)->update(['todays_deal' => 0]);
        }

        Cache::forget('todays_deal_products');

        return response()->json(['success' => true]);
    }

    public function search(Request $request)
    {
        $todays_deal = 1;
        $products = $this->productService->todays_deal_products_search($request->except(['_token']), $todays_deal);
        $single_select = $request->single_select ?? 0;

        return view('backend.promotion_and_offers.todays_deal.products_search', compact('products', 'single_select', 'todays_deal'));
    }
}

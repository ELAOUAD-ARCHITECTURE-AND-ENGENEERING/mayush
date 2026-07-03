<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PromotionalProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
        $this->middleware(['permission:view_promotion_and_offers_dashboard'])->only('dashboard');
        $this->middleware(['permission:view_promotional_product'])->only('index', 'filter');
        $this->middleware(['permission:add_promotional_products'])->only('search', 'update');
    }

    public function index()
    {
        $seller_type = '';
        $categories = Category::where('parent_id', 0)->with('childrenCategories')->get();
        $product_types = ['Promotional Product List'];

        return view('backend.promotion_and_offers.index', compact('seller_type', 'categories', 'product_types'));
    }

    public function dashboard()
    {
        $today = strtotime(date('d-m-Y'));
        $couponQuery = Schema::hasTable('coupons') ? Coupon::query() : null;

        return view('backend.promotion_and_offers.dashboard', [
            'totalProducts' => Product::where('approved', 1)->where('published', 1)->count(),
            'promotionalProducts' => Product::where('auction_product', 0)->where('wholesale_product', 0)->where('promotional', 1)->count(),
            'totalFlashDeals' => FlashDeal::count(),
            'activeFlashDeals' => FlashDeal::where('status', 1)->count(),
            'todaysDeal' => Product::where('promotional', 1)->where('todays_deal', 1)->count(),
            'all_categories' => Category::count(),
            'main_categories' => Category::where('parent_id', 0)->count(),
            'totalCoupons' => $couponQuery ? (clone $couponQuery)->count() : 0,
            'activeCoupons' => $couponQuery ? (clone $couponQuery)->where(function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)
                        ->where('type', '!=', 'welcome_base');
                })->orWhere(function ($q) {
                    $q->where('type', 'welcome_base')->where('status', 1);
                });
            })->count() : 0,
        ]);
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
            Product::whereIn('id', $checkedIds)->update(['promotional' => 1]);
        }

        if ($uncheckedIds !== []) {
            Product::whereIn('id', $uncheckedIds)->update(['promotional' => 0, 'todays_deal' => 0]);
            FlashDealProduct::whereIn('product_id', $uncheckedIds)->delete();
        }

        Cache::forget('todays_deal_products');

        return response()->json(['success' => true]);
    }

    public function search(Request $request)
    {
        $promotional = 1;
        $products = $this->productService->promotional_products_search($request->except(['_token']), $promotional);
        $single_select = $request->single_select ?? 0;

        return view('backend.promotion_and_offers.products_search', compact('products', 'single_select', 'promotional'));
    }

    public function filter(Request $request)
    {
        $products = Product::query()
            ->where('auction_product', 0)
            ->where('wholesale_product', 0)
            ->where('promotional', 1);

        if ($request->product_type === 'drafts') {
            $products->where('draft', 1)->where('added_by', 'admin');
        } else {
            $products->where('draft', 0);
            $this->applyProductFilters($products, $request);
        }

        if ($request->filled('search')) {
            $sort_search = $request->search;
            $products->where(function ($query) use ($sort_search) {
                $query->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhereHas('stocks', function ($q) use ($sort_search) {
                        $q->where('sku', 'like', '%' . $sort_search . '%');
                    });
            });
        } else {
            $sort_search = null;
        }

        $col_name = null;
        $query = null;
        if ($request->filled('type')) {
            [$col_name, $query] = explode(',', $request->type);
            if (in_array($col_name, ['created_at', 'updated_at', 'name', 'unit_price'], true) && in_array($query, ['asc', 'desc'], true)) {
                $products->orderBy($col_name, $query);
            }
        }

        $products = $products->orderBy('updated_at', 'desc')->paginate(15);
        $type = $request->seller_type;
        $ptoduct_type = $request->product_type;

        $view = view('backend.promotion_and_offers.filter', compact(
            'products',
            'type',
            'col_name',
            'query',
            'sort_search',
            'ptoduct_type'
        ))->render();

        return response()->json(['html' => $view]);
    }

    private function applyProductFilters($products, Request $request): void
    {
        if ($request->seller_type === 'admin') {
            $products->where('added_by', 'admin');
        } elseif ($request->seller_type === 'seller') {
            $products->where('added_by', 'seller');
            if ($request->filled('user_id')) {
                $products->where('user_id', $request->user_id);
            }
        }

        if ($request->product_type === 'digital_products') {
            $products->where('digital', 1);
        } elseif ($request->product_type === 'physical_products') {
            $products->where('digital', 0);
        } elseif ($request->product_type === 'not_approved') {
            $products->where('approved', 0);
        } elseif ($request->product_type === 'pos_product_list') {
            $products->where('pos', 1);
        } elseif ($request->product_type === 'todays_deal_product_list') {
            $products->where('todays_deal', 1);
        }

        if ($request->filled('brand_id')) {
            $products->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $products->whereHas('categories', fn ($query) => $query->where('categories.id', $request->category_id));
        }
    }
}

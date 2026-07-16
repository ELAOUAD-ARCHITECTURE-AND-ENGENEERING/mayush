<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\PreorderProduct;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FrontendShopController extends Controller
{
    public function shop($slug)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if ($shop != null) {
            abort_unless($shop->isPubliclyVisible(), 404);

            return view('frontend.seller_shop', compact('shop'));
        }
        abort(404);
    }

    public function filter_shop(Request $request, $slug, $type)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if ($shop != null && $type != null) {
            abort_unless($shop->isPubliclyVisible(), 404);
            if ($type == 'all-products') {
                $sort_by = $request->sort_by;
                $min_price = $request->min_price;
                $max_price = $request->max_price;
                $selected_categories = array();
                $brand_id = null;
                $rating = null;

                $conditions = ['user_id' => $shop->user->id, 'published' => 1, 'approved' => 1];

                if ($request->brand != null) {
                    $brand_id = (Brand::where('slug', $request->brand)->first() != null) ? Brand::where('slug', $request->brand)->first()->id : null;
                    $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
                }

                $products = Product::publiclyVisible()->where($conditions);

                if ($request->has('selected_categories')) {
                    $selected_categories = $request->selected_categories;
                    $products->whereIn('category_id', $selected_categories);
                }

                if ($min_price != null && $max_price != null) {
                    $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
                }

                if ($request->has('rating')) {
                    $rating = $request->rating;
                    $products->where('rating', '>=', $rating);
                }

                switch ($sort_by) {
                    case 'newest':
                        $products->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $products->orderBy('created_at', 'asc');
                        break;
                    case 'price-asc':
                        $products->orderBy('unit_price', 'asc');
                        break;
                    case 'price-desc':
                        $products->orderBy('unit_price', 'desc');
                        break;
                    default:
                        $products->orderBy('id', 'desc');
                        break;
                }

                $products = $products->paginate(24)->appends(request()->query());

                return view('frontend.seller_shop', compact('shop', 'type', 'products', 'selected_categories', 'min_price', 'max_price', 'brand_id', 'sort_by', 'rating'));
            }

            if ($type == 'all-preorder-products') {
                $sort_by = $request->sort_by;
                $min_price = $request->min_price;
                $max_price = $request->max_price;
                $selected_categories = array();
                $is_available = array();
                $brand_id = null;
                $rating = null;

                $conditions = ['user_id' => $shop->user->id, 'is_published' => 1];

                if ($request->brand != null) {
                    $brand_id = (Brand::where('slug', $request->brand)->first() != null) ? Brand::where('slug', $request->brand)->first()->id : null;
                    $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
                }

                $products = PreorderProduct::publiclyVisible()->where('user_id', $shop->user->id);

                if ($request->has('is_available') && $request->is_available !== null) {
                    $availability = $request->is_available;
                    $currentDate = Carbon::now()->format('Y-m-d');
                    $products->where(function ($query) use ($availability, $currentDate) {
                        if ($availability == 1) {
                            $query->where('is_available', 1)->orWhere('available_date', '<=', $currentDate);
                        } else {
                            $query->where(function ($query) {
                                $query->where('is_available', '!=', 1)
                                    ->orWhereNull('is_available');
                            })
                                ->where(function ($query) use ($currentDate) {
                                    $query->whereNull('available_date')
                                        ->orWhere('available_date', '>', $currentDate);
                                });
                        }
                    });

                    $is_available = $availability;
                } else {
                    $is_available = null;

                }


                if ($request->has('selected_categories')) {
                    $selected_categories = $request->selected_categories;
                    $products->whereIn('category_id', $selected_categories);
                }

                if ($min_price != null && $max_price != null) {
                    $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
                }

                if ($request->has('rating')) {
                    $rating = $request->rating;
                    $products->where('rating', '>=', $rating);
                }

                switch ($sort_by) {
                    case 'newest':
                        $products->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $products->orderBy('created_at', 'asc');
                        break;
                    case 'price-asc':
                        $products->orderBy('unit_price', 'asc');
                        break;
                    case 'price-desc':
                        $products->orderBy('unit_price', 'desc');
                        break;
                    default:
                        $products->orderBy('id', 'desc');
                        break;
                }

                $products = $products->paginate(24)->appends(request()->query());

                return view('frontend.seller_shop', compact('shop', 'type', 'products', 'selected_categories', 'min_price', 'max_price', 'brand_id', 'sort_by', 'rating','is_available'));
            }

            return view('frontend.seller_shop', compact('shop', 'type'));
        }
        abort(404);
    }

    public function all_seller(Request $request)
    {
        $shops = Shop::publiclyVisible()
            ->paginate(15);

        return view('frontend.shop_listing', compact('shops'));
    }
}

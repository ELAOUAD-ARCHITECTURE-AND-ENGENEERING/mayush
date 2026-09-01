<?php

namespace App\Http\Controllers\Api\V2;

use Cache;
use App\Models\Shop;
use App\Models\Color;
use App\Models\Product;
use App\Models\FlashDeal;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use App\Utility\CategoryUtility;
use App\Http\Resources\V2\FlashDealCollection;
use App\Http\Resources\V2\LastViewedProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Models\Brand;
use App\Models\Category;
use App\Http\Resources\V2\Seller\BrandCollection;
use App\Services\SearchQueryNormalizer;
use App\Utility\CartUtility;
class ProductController extends Controller
{
    public function index()
    {
        return new ProductMiniCollection(Product::publiclyVisible()->latest()->paginate(10));
    }
    public function show($id)
    {
        $product = Product::publiclyVisible()->where('id', $id)->get();

        if ($product->isEmpty()) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => translate('Product not found'),
            ], 404);
        }

        return new ProductDetailCollection($product);
    }

    public function product_details($slug, $user_id)
    {
        $product = Product::publiclyVisible()->where('slug', $slug)->get();
        if ($product->isEmpty()) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        if(get_setting('last_viewed_product_activation') == 1 && $user_id != null){
            lastViewedProducts($product[0]->id, $user_id);
        }
        return new ProductDetailCollection($product);
    }

    public function getPrice(Request $request)
    {
        $product = Product::publiclyVisible()->where(function ($q) use ($request) {
            $q->where("slug", $request->slug);
            if (is_numeric($request->slug)) {
                $q->orWhere('id', (int) $request->slug);
            }
            if ($request->has('id') && is_numeric($request->id)) {
                $q->orWhere('id', (int) $request->id);
            }
        })->first();
        if (!$product) {
            return response()->json(['result' => false, 'message' => translate('Product not found')], 404);
        }

        $str = '';
        $tax = 0;
        $quantity = 1;



        if ($request->has('quantity') && $request->quantity != null) {
            $quantity = $request->quantity;
        }

        if ($request->has('color') && $request->color != null) {
            $str = Color::where('code', '#' . $request->color)->first()->name;
        }

        $var_str = str_replace(',', '-', $request->variants);
        $var_str = str_replace(' ', '', $var_str);

        if ($var_str != "") {
            $temp_str = $str == "" ? $var_str : '-' . $var_str;
            $str .= $temp_str;
        }

        $product_stock = CartUtility::find_product_stock($product, $str);
        if (!$product_stock) {
            $product_stock = $product->stocks->first();
        }
        if (!$product_stock) {
            return response()->json(['result' => false, 'message' => translate('Variant not found')], 404);
        }

        $price = $product_stock->price;


        if ($product->wholesale_product) {
            $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $quantity)->where('max_qty', '>=', $quantity)->first();
            if ($wholesalePrice) {
                $price = $wholesalePrice->price;
            }
        }

        $stock_qty = $product_stock->qty;
        $stock_txt = $product_stock->qty;
        $max_limit = $product_stock->qty;

        if ($stock_qty >= 1 && $product->min_qty <= $stock_qty) {
            $in_stock = 1;
        } else {
            $in_stock = 0;
        }

        //Product Stock Visibility
        if ($product->stock_visibility_state == 'text') {
            if ($stock_qty >= 1 && $product->min_qty < $stock_qty) {
                $stock_txt = translate('In Stock');
            } else {
                $stock_txt = translate('Out Of Stock');
            }
        }

        //discount calculation
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        // taxes
        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        $price += $tax;

        return response()->json(

            [
                'result' => true,
                'data' => [
                    'price' => single_price($price * $quantity),
                    'stock' => $stock_qty,
                    'stock_txt' => $stock_txt,
                    'digital' => $product->digital,
                    'variant' => $str,
                    'variation' => $str,
                    'max_limit' => $max_limit,
                    'in_stock' => $in_stock,
                    'image' => $product_stock->image == null ? "" : uploaded_asset($product_stock->image)
                ]

            ]
        );
    }

    public function seller($id, Request $request)
    {
        $shop = Shop::findOrFail($id);
        if (!$shop->isPubliclyVisible()) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        $products = Product::publiclyVisible()->where('added_by', 'seller')->where('user_id', $shop->user_id);
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1);
        return new ProductMiniCollection($products->latest()->paginate(10));
    }

    public function categoryProducts($slug, Request $request)
    {
        $category = Category::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int)$slug : 0)
            ->first();
        if (!$category) {
            return new ProductMiniCollection(collect());
        }

        $category_ids = array_merge([$category->id], CategoryUtility::children_ids($category->id));

        $products = Product::query()->where(function ($query) use ($category_ids) {
            $query->whereIn('category_id', $category_ids)
                ->orWhereHas('product_categories', function ($q) use ($category_ids) {
                    $q->whereIn('category_id', $category_ids);
                });
        });

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = min(max($perPage, 10), 50);

        return new ProductMiniCollection(filter_products($products)->latest()->paginate($perPage));
    }

    public function subCategory($id, Request $request)
    {
        $category = is_numeric($id) ? Category::find((int)$id) : Category::where('slug', $id)->first();
        if (!$category) {
            return new ProductMiniCollection(collect());
        }

        $category_ids = array_merge([$category->id], CategoryUtility::children_ids($category->id));

        $products = Product::query()->where(function ($query) use ($category_ids) {
            $query->whereIn('category_id', $category_ids)
                ->orWhereHas('product_categories', function ($q) use ($category_ids) {
                    $q->whereIn('category_id', $category_ids);
                });
        });

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = min(max($perPage, 10), 50);

        return new ProductMiniCollection(filter_products($products)->latest()->paginate($perPage));
    }

    public function brand($slug, Request $request)
    {
        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) {
            return new ProductMiniCollection(collect());
        }
        $products = Product::where('brand_id', $brand->id)->physical();
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(10));
    }

    public function getBrands()
    {
        $brands = Brand::all();

        return BrandCollection::collection($brands);
    }

    public function todaysDeal()
    {
        $products = Product::where('todays_deal', 1)->physical();
        return new ProductMiniCollection(filter_products($products)->limit(20)->latest()->get());
    }

    public function flashDeal()
    {
        return Cache::remember('app.flash_deals', 86400, function () {
            $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
            return new FlashDealCollection($flash_deals);
        });
    }

    public function featured()
    {
        $products = Product::where('featured', 1)->physical();
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(10));
    }

    public function inhouse()
    {
        $products = Product::where('added_by', 'admin');
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(12));
    }

    public function digital()
    {
        $products = Product::digital();
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(10));
    }

    public function bestSeller()
    {
        $products = Product::orderBy('num_of_sale', 'desc')->physical();
        return new ProductMiniCollection(filter_products($products)->limit(20)->get());
    }

    public function frequentlyBought($slug)
    {
        $product = Product::publiclyVisible()->where(function ($q) use ($slug) {
            $q->where("slug", $slug);
            if (is_numeric($slug)) {
                $q->orWhere('id', (int) $slug);
            }
        })->first();
        if (!$product) {
            return new ProductMiniCollection(collect());
        }
        $products = get_frequently_bought_products($product);
        $isEmpty = empty($products) 
            || (is_object($products) && method_exists($products, 'isEmpty') && $products->isEmpty())
            || (is_countable($products) && count($products) === 0);

        if ($isEmpty && $product->category_id) {
            $products = Product::publiclyVisible()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->limit(6)
                ->get();
        }
        return new ProductMiniCollection(is_array($products) ? collect($products) : $products);
    }

    public function topFromSeller($slug)
    {
        $product = Product::publiclyVisible()->where(function ($q) use ($slug) {
            $q->where("slug", $slug);
            if (is_numeric($slug)) {
                $q->orWhere('id', (int) $slug);
            }
        })->first();
        if (!$product) {
            return new ProductMiniCollection(collect());
        }
        $products = Product::publiclyVisible()->where('user_id', $product->user_id)->where('id', '!=', $product->id)->orderBy('num_of_sale', 'desc')->physical();
        return new ProductMiniCollection(filter_products($products)->limit(10)->get());
    }


    public function search(Request $request)
    {
        $category_ids = [];
        $brand_ids = [];

        if ($request->categories != null && $request->categories != "") {
            $category_ids = explode(',', $request->categories);
        }

        if ($request->brands != null && $request->brands != "") {
            $brand_ids = explode(',', $request->brands);
        }

        $sort_by = $request->sort_key ?? $request->sort_by;
        $rawName = $request->name;
        $normalizedName = app(SearchQueryNormalizer::class)->normalize($rawName);
        $name = $normalizedName['is_truncated'] ? '' : $normalizedName['normalized'];
        $min = $request->min;
        $max = $request->max;


        $products = Product::query();

        $products->where('published', 1);

        if ($normalizedName['is_truncated']) {
            $products->whereRaw('1 = 0');
        }

        if ($request->digital == 1) {
            $products->digital();
        } else {
            $products->physical();
        }

        if ($request->has('has_discount') && $request->has_discount == 1) {
            $products->where(function ($q) {
                $q->where('discount', '>', 0)
                  ->orWhere('todays_deal', 1);
            });
        }


        if (!empty($brand_ids)) {
            $products->whereIn('brand_id', $brand_ids);
        }

        if (!empty($category_ids)) {
            $n_cid = [];
            foreach ($category_ids as $cid) {
                $n_cid = array_merge($n_cid, CategoryUtility::children_ids($cid));
            }

            if (!empty($n_cid)) {
                $category_ids = array_merge($category_ids, $n_cid);
            }

            $products->whereIn('category_id', $category_ids);
        }

        if ($name != null && $name != "") {
            $terms = array_slice($normalizedName['tokens'], 0, (int) config('search.query.max_terms', 12));
            $products->where(function ($query) use ($terms) {
                foreach ($terms as $word) {
                    $query->where(function ($termQuery) use ($word) {
                        $termQuery->where('name', 'like', '%' . $word . '%')
                            ->orWhere('tags', 'like', '%' . $word . '%')
                            ->orWhereHas('product_translations', function ($translationQuery) use ($word) {
                                $translationQuery->where('name', 'like', '%' . $word . '%');
                            });
                    });
                }
            });
            SearchUtility::store($rawName);
            $case1 = $name . '%';
            $case2 = '%' . $name . '%';

            if (config('search.features.improved_mysql', false)) {
                $products->orderByRaw('CASE
                    WHEN name LIKE ? THEN 1
                    WHEN name LIKE ? THEN 2
                    ELSE 3
                    END', [$case1, $case2]);
            }
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }



        switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('num_of_sale', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->orderBy('created_at', 'desc');
                break;
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = min(max($perPage, 10), 50);

        return new ProductMiniCollection(filter_products($products)->paginate($perPage));
    }

    public function variantPrice(Request $request)
    {
        $product = Product::publiclyVisible()->findOrFail($request->id);
        $str = '';
        $tax = 0;

        if ($request->has('color') && $request->color != "") {
            $str = Color::where('code', '#' . $request->color)->first()->name;
        }

        $var_str = str_replace(',', '-', $request->variants);
        $var_str = str_replace(' ', '', $var_str);

        if ($var_str != "") {
            $temp_str = $str == "" ? $var_str : '-' . $var_str;
            $str .= $temp_str;
        }
        return   $this->calc($product, $str, $request, $tax);
    }

    public function lastViewedProducts(){
        $lastViewedProducts = getLastViewedProducts();
        return new LastViewedProductCollection( $lastViewedProducts);
    }
}

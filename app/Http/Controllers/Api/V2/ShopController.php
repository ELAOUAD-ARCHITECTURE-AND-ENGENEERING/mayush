<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ShopCollection;
use App\Http\Resources\V2\ShopDetailsCollection;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Utility\SearchUtility;
use Cache;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shop_query = Shop::publiclyVisible();

        if ($request->name != null && $request->name != "") {
            $shop_query->where("name", 'like', "%{$request->name}%");
            SearchUtility::store($request->name);
        }

        return new ShopCollection($shop_query->paginate(10));

        //remove this , this is for testing
        //return new ShopCollection($shop_query->paginate(10));
    }

    public function info($id)
    {
        $shop = Shop::where('slug', $id)->first();
        if (!$shop || !$shop->isPubliclyVisible()) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        return new ShopDetailsCollection($shop);
    }

    public function shopOfUser($id)
    {
        return new ShopCollection(Shop::publiclyVisible()->where('user_id', $id)->get());
    }

    public function allProducts($id)
    {
        $shop = Shop::findOrFail($id);
        if (!$shop->isPubliclyVisible()) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        return new ProductCollection(Product::publiclyVisible()->where('user_id', $shop->user_id)->latest()->paginate(10));
    }

    public function topSellingProducts($id)
    {
        $shop = Shop::findOrFail($id);
        if (!$shop->isPubliclyVisible()) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        return Cache::remember("app.top_selling_products-$id", 86400, function () use ($shop) {
            return new ProductMiniCollection(Product::publiclyVisible()->where('user_id', $shop->user_id)->orderBy('num_of_sale', 'desc')->limit(10)->get());
        });
    }

    public function featuredProducts($id)
    {
        $shop = Shop::findOrFail($id);
        if (!$shop->isPubliclyVisible()) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        return Cache::remember("app.featured_products-$id", 86400, function () use ($shop) {
            return new ProductMiniCollection(Product::publiclyVisible()->where(['user_id' => $shop->user_id, 'seller_featured' => 1])->latest()->limit(10)->get());
        });
    }

    public function newProducts($id)
    {
        $shop = Shop::findOrFail($id);
        if (!$shop->isPubliclyVisible()) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        return Cache::remember("app.new_products-$id", 86400, function () use ($shop) {
            return new ProductMiniCollection(Product::publiclyVisible()->where('user_id', $shop->user_id)->orderBy('created_at', 'desc')->limit(10)->get());
        });
    }

    public function brands($id)
    {
    }
}

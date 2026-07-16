<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\FlashDealBannerCollection;
use App\Http\Resources\V2\FlashDealCollection;
use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Models\FlashDeal;
use App\Models\Product;

class FlashDealController extends Controller
{
    public function index()
    {
        $flash_deals = FlashDeal::where('status', 1)
            ->where('start_date', '<=', strtotime(date('d-m-Y')))
            ->where('end_date', '>=', strtotime(date('d-m-Y')))
            ->orderBy('created_at', 'desc')
            ->get();

        return new FlashDealCollection($flash_deals);
    }
    public function info($slug)
    {
        $flash_deals = FlashDeal::where('slug', $slug)->where('status', 1)
            ->where('start_date', '<=', strtotime(date('d-m-Y')))
            ->where('end_date', '>=', strtotime(date('d-m-Y')))
            ->get();

        return new FlashDealCollection($flash_deals);
    }
    public function banners()
    {
        $flash_deals = FlashDeal::where('status', 1)
            ->where('start_date', '<=', strtotime(date('d-m-Y')))
            ->where('end_date', '>=', strtotime(date('d-m-Y')))
            ->get();

        return new FlashDealBannerCollection($flash_deals);
    }

    public function products($id)
    {
        $flash_deal = FlashDeal::where("slug", $id)->first();
        $products = collect();
        if (!$flash_deal) {
            return new ProductMiniCollection($products);
        }
        foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product) {
            $product = Product::publiclyVisible()->find($flash_deal_product->product_id);
            if ($product) {
                $products->push($product);
            }
        }
        return new ProductMiniCollection($products);
    }
}

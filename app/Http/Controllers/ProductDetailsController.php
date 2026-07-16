<?php

namespace App\Http\Controllers;

use Auth;
use Cache;
use Cookie;
use App\Models\Product;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\OrderDetail;
use App\Models\ProductQuery;
use Illuminate\Http\Request;
use App\Models\AffiliateConfig;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Session;
use Carbon\Carbon;
use App\Models\LastViewedProduct;
use App\Models\FlashDealProduct;
use App\Models\FrequentlyBoughtProduct;
use App\Models\User;
use App\Models\ProductView;
use App\Http\Controllers\AffiliateController;
use App\Utility\CartUtility;

class ProductDetailsController extends Controller
{
    public function product(Request $request, $slug)
    {
        if (!Auth::check()) {
            session(['link' => url()->current()]);
        }

        $detailedProduct  = Product::publiclyVisible()
            ->with('reviews', 'brand', 'stocks', 'user', 'user.shop')
            ->where('auction_product', 0)
            ->where('slug', $slug)
            ->first();

        if ($detailedProduct != null && $detailedProduct->published) {
            // Time-series view tracking (MA-104)
            $view_session_key = 'viewed_product_' . $detailedProduct->id;
            if (!Session::has($view_session_key)) {
                $detailedProduct->increment('num_of_view');
                \App\Models\ProductView::create([
                    'product_id' => $detailedProduct->id,
                    'user_id'    => Auth::check() ? Auth::id() : null,
                    'ip_address' => $request->ip(),
                    'session_id' => Session::getId(),
                ]);
                Session::put($view_session_key, time());
            }
            if ((get_setting('vendor_system_activation') != 1) && $detailedProduct->added_by == 'seller') {
                abort(404);
            }

            if ($detailedProduct->added_by == 'seller' && $detailedProduct->user->banned == 1) {
                abort(404);
            }

            if (!addon_is_activated('wholesale') && $detailedProduct->wholesale_product == 1) {
                abort(404);
            }

            $product_queries = ProductQuery::where('product_id', $detailedProduct->id)->where('customer_id', '!=', Auth::id())->whereNotNull('reply')->latest('id')->paginate(3);
            $total_query = ProductQuery::where('product_id', $detailedProduct->id)->whereNotNull('reply')->count();
            $reviews = $detailedProduct->reviews()->where('status', 1)->orderBy('created_at', 'desc')->paginate(3);

            // Pagination using Ajax
            if (request()->ajax()) {
                if ($request->type == 'query') {
                    return Response::json(View::make('frontend.partials.product_query_pagination', array('product_queries' => $product_queries))->render());
                }
                if ($request->type == 'review') {
                    return Response::json(View::make('frontend.product_details.reviews', array('reviews' => $reviews))->render());
                }
            }

            // review status
            $review_status = 0;
            $order_id = '';
            if (Auth::check()) {
                $OrderDetail = OrderDetail::with(['order' => function ($q) {
                    $q->where('user_id', Auth::id());
                }])->where('product_id', $detailedProduct->id)->where('delivery_status', 'delivered')->first();
                $review_status = $OrderDetail ? 1 : 0;
                $order_id = $OrderDetail->order->id ?? null ;
            }
            if ($request->has('product_referral_code') && addon_is_activated('affiliate_system')) {
                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24;
                if ($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }
                Cookie::queue('product_referral_code', $request->product_referral_code, $cookie_minute);
                Cookie::queue('referred_product_id', $detailedProduct->id, $cookie_minute);

                $referred_by_user = User::where('referral_code', $request->product_referral_code)->first();

                if ($referred_by_user) {
                    $affiliateController = new AffiliateController;
                    $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
                }
            }

            if(get_setting('last_viewed_product_activation') == 1 && Auth::check() && isCustomer()){
                lastViewedProducts($detailedProduct->id, auth()->user()->id);
            }

            // Smart Recommendations
            
            // 1. Deals on related products
            $related_deals = filter_products(Product::query())
                ->where('discount', '>', 0)
                ->where('category_id', $detailedProduct->category_id)
                ->where('id', '!=', $detailedProduct->id)
                ->limit(6)
                ->get();

            // 2. Customers who viewed this item also viewed
            $users_who_viewed = LastViewedProduct::where('product_id', $detailedProduct->id)->pluck('user_id');
            $also_viewed = collect();
            if ($users_who_viewed->isNotEmpty()) {
                $also_viewed = filter_products(Product::query())
                    ->whereIn('id', function ($query) use ($users_who_viewed, $detailedProduct) {
                        $query->select('product_id')
                            ->from('last_viewed_products')
                            ->whereIn('user_id', $users_who_viewed)
                            ->where('product_id', '!=', $detailedProduct->id)
                            ->groupBy('product_id')
                            ->orderByRaw('count(*) desc');
                    })
                    ->limit(6)
                    ->get();
            }

            // 3. Best Selling products in the same category
            $category_best_sellers = filter_products(Product::query())
                ->where('category_id', $detailedProduct->category_id)
                ->where('id', '!=', $detailedProduct->id)
                ->orderBy('num_of_sale', 'desc')
                ->limit(6)
                ->get();

            // 4. Browsing history recommendations
            $history_recommendations = collect();
            if (Auth::check()) {
                $user_history_ids = LastViewedProduct::where('user_id', Auth::id())->pluck('product_id');
                if ($user_history_ids->isNotEmpty()) {
                    $other_users = LastViewedProduct::whereIn('product_id', $user_history_ids)
                        ->where('user_id', '!=', Auth::id())
                        ->pluck('user_id');
                    
                    if ($other_users->isNotEmpty()) {
                        $history_recommendations = filter_products(Product::query())
                            ->whereIn('id', function ($query) use ($other_users, $user_history_ids) {
                                $query->select('product_id')
                                    ->from('last_viewed_products')
                                    ->whereIn('user_id', $other_users)
                                    ->whereNotIn('product_id', $user_history_ids)
                                    ->groupBy('product_id')
                                    ->orderByRaw('count(*) desc');
                            })
                            ->limit(6)
                            ->get();
                    }
                }
            } // ends if(Auth::check())

            // 5. Frequently Bought Together (MA-107)
            $frequently_bought = filter_products(Product::query())
                ->whereIn('id', function ($query) use ($detailedProduct) {
                    $query->select('frequently_bought_product_id')
                        ->from('frequently_bought_products')
                        ->where('product_id', $detailedProduct->id)
                        ->orderBy('affinity_score', 'desc');
                })
                ->limit(5)
                ->get();

            return view('frontend.product_details', compact(
                'detailedProduct', 
                'product_queries', 
                'total_query', 
                'reviews', 
                'review_status', 
                'order_id',
                'related_deals',
                'also_viewed',
                'category_best_sellers',
                'history_recommendations',
                'frequently_bought'
            ));
        }
        abort(404);
    }

    public function variant_price(Request $request)
    {
        $product = Product::publiclyVisible()->find($request->id);
        if (!$product) {
            abort(404);
        }
        $str = '';
        $quantity = 0;
        $tax = 0;
        $max_limit = 0;

        if ($request->has('color')) {
            $str = $request['color'];
        }

        if (json_decode($product->choice_options) != null) {
            foreach (json_decode($product->choice_options) as $key => $choice) {
                if ($str != null) {
                    $str .= '-' . str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                } else {
                    $str .= str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                }
            }
        }

        $product_stock = CartUtility::find_product_stock($product, $str);
        if (!$product_stock) {
            abort(404);
        }

        $price = $product_stock->price;


        if ($product->wholesale_product) {
            $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
            if ($wholesalePrice) {
                $price = $wholesalePrice->price;
            }
        }

        $quantity = $product_stock->qty;
        $max_limit = $product_stock->qty;

        if ($quantity >= 1 && $product->min_qty <= $quantity) {
            $in_stock = 1;
        } else {
            $in_stock = 0;
        }

        //Product Stock Visibility
        if ($product->stock_visibility_state == 'text') {
            if ($quantity >= 1 && $product->min_qty < $quantity) {
                $quantity = translate('In Stock');
            } else {
                $quantity = translate('Out Of Stock');
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
        if (addon_is_activated('gst_system')) {
        $price += ($price * $product->gst_rate) / 100;
        }

        $sku= $product_stock->sku ?? 'N/A';

        return array(
            'price' => single_price($price * $request->quantity),
            'quantity' => $quantity,
            'digital' => $product->digital,
            'variation' => $str,
            'max_limit' => $max_limit,
            'in_stock' => $in_stock,
            'sku'      => $sku,
            'length'   => $product_stock->length,
            'width'    => $product_stock->width,
            'height'   => $product_stock->height,
            'dimension_unit' => $product_stock->dimension_unit ?: 'cm',
        );
    }

    public function flash_deal_details($slug)
    {
        $flash_deal = $this->storefrontFlashDeals()
            ->where('slug', $slug)
            ->firstOrFail();
        $all_flash_deals = $this->storefrontFlashDeals()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.flash_deal.modern_flash_deal_details', compact('flash_deal', 'all_flash_deals'));
    }

    public function flash_deal_details_grid($slug)
    {
        $flash_deal = $this->storefrontFlashDeals()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.flash_deal.partials.single_deal_product_grid', compact('flash_deal'));
    }

    public function trackOrder(Request $request)
    {
        if ($request->has('order_code')) {
            $order = \App\Models\Order::where('code', $request->order_code)->first();
            if ($order != null) {
                return view('frontend.track_order', compact('order'));
            }
        }
        return view('frontend.track_order');
    }

    public function all_flash_deals()
    {
        $all_flash_deals = $this->storefrontFlashDeals()
            ->orderBy('created_at', 'desc')
            ->get();
        $flash_deal_products = $this->uniqueFlashDealProducts($all_flash_deals);

        $fallback_best_sellers = collect();
        $fallback_suggested = collect();

        if ($all_flash_deals->isEmpty()) {
            $fallback_best_sellers = filter_products(Product::query())
                ->orderBy('num_of_sale', 'desc')
                ->limit(10)
                ->get();
            $fallback_suggested = filter_products(Product::query())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view("frontend.flash_deal.modern_all_flash_deal_list", compact(
            'all_flash_deals',
            'flash_deal_products',
            'fallback_best_sellers',
            'fallback_suggested'
        ));
    }

    public function flash_deals_grid()
    {
        $all_flash_deals = $this->storefrontFlashDeals()
            ->orderBy('created_at', 'desc')
            ->get();
        $flash_deal_products = $this->uniqueFlashDealProducts($all_flash_deals);

        return view("frontend.flash_deal.partials.product_grid", compact('flash_deal_products'));
    }

    private function storefrontFlashDeals()
    {
        $visibleProducts = fn ($query) => filter_products($query);

        return FlashDeal::active()
            ->whereHas('flash_deal_products.product', $visibleProducts)
            ->with([
                'flash_deal_products' => function ($query) use ($visibleProducts) {
                    $query->whereHas('product', $visibleProducts)
                        ->with([
                            'product' => function ($query) {
                                filter_products($query)->with(['stocks', 'user', 'reviews']);
                            },
                        ]);
                },
            ]);
    }

    private function uniqueFlashDealProducts($flashDeals)
    {
        return $flashDeals
            ->flatMap->flash_deal_products
            ->filter(fn ($flashDealProduct) => $flashDealProduct->product != null)
            ->unique('product_id')
            ->values();
    }

    public function todays_deal()
    {
        $todays_deal_products = Cache::rememberForever('todays_deal_products', function () {
            return filter_products(Product::with('thumbnail')->where('todays_deal', '1'))->get();
        });

        return view("frontend.todays_deal", compact('todays_deal_products'));
    }

    public function best_selling()
    {
        $best_selling_products =  filter_products(Product::orderBy('num_of_sale', 'desc'))->take(18)->get();
        return view("frontend.best_selling", compact('best_selling_products'));
    }

    public function featured_products()
    {
        $featured_products =  filter_products(Product::where('featured', '1'))->latest()->limit(12)->get();
        return view("frontend.featured_products", compact('featured_products'));
    }

    public function product_reviews(Request $request) {
        $detailedProduct = Product::publiclyVisible()
            ->where('slug', $request->slug)
            ->firstOrFail();
        $query = $detailedProduct->reviews()->where('status', 1);
        switch ($request->sort_by) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'higest':
                $query->orderBy('rating', 'desc');
                break;
            case 'lowest':
                $query->orderBy('rating', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $limit = (int) $request->limit ?: 3;
        $totalReviews = $detailedProduct->reviews()->where('status', 1)->count();
        if ($request->has('rating') && $request->rating != '') {
            $query->where('rating', $request->rating);
        }
        $reviews = $query->take($limit)->get();

        return response()->json([
            'html' => view('frontend.product_details.reviews', compact('reviews'))->render(),
            'has_more' => $totalReviews > $limit
        ]);
    }
}

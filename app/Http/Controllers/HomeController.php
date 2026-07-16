<?php

namespace App\Http\Controllers;

use Auth;
use Hash;
use Cache;
use Cookie;
use App\Models\Page;
use App\Models\User;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\OrderDetail;
use App\Models\ProductQuery;
use Illuminate\Http\Request;
use App\Models\AffiliateConfig;
use App\Models\Blog;
use App\Models\CustomerPackage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\Cart;
use App\Models\Preorder;
use App\Rules\Recaptcha;
use Illuminate\Validation\Rule;
use App\Models\PreorderProduct;
use App\Models\RegistrationVerificationCode;
use App\Models\SmsTemplate;
use App\Services\SendSmsService;
use App\Utility\EmailUtility;
use Artisan;
use DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use ZipArchive;
use Session;
use App\Models\LastViewedProduct;
use App\Services\HomeLayoutService;
use App\Services\AuthService;

class HomeController extends Controller
{
    protected $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the application frontend home.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, HomeLayoutService $layoutService)
    {
        $this->authService->processRegistrationReferral($request);

        $homepageData = $layoutService->getHomepageData();

        $authUser = Auth::user();
        if (get_setting('portfolio_landing')) {
            $goingons = $layoutService->getPortfolioGoingOns();
            if (!auth()->check()) {
                return view('frontend.portfolio.index', $homepageData + compact('goingons'));
            }
            $sellerOnboardingRestricted = $authUser->user_type === 'seller'
                && (!$authUser->shop || !$authUser->shop->isFullyApproved());

            if (($authUser->user_type !== 'seller' && $authUser->verification_status == 0) || $sellerOnboardingRestricted) {
                return view('frontend.portfolio.index', $homepageData + compact('goingons'));
            }
        }

        return view('frontend.' . safe_homepage_select() . '.index', $homepageData);
    }

    public function load_todays_deal_section(HomeLayoutService $layoutService)
    {
        if (get_setting('todays_deal_section_status', '1') != '1') {
            return response('');
        }

        $todays_deal_products = $layoutService->getTodaysDealProducts();
        return view('frontend.' . safe_homepage_select() . '.partials.todays_deal', compact('todays_deal_products'));
    }

    public function load_newest_product_section(Request $request, HomeLayoutService $layoutService)
    {
        $newest_products = $layoutService->getNewestProducts(12, $request->page);
        return view('frontend.' . safe_homepage_select() . '.partials.newest_products_section', compact('newest_products'));
    }

    public function load_featured_section()
    {
        if (get_setting('featured_products_section_status', '1') != '1') {
            return response('');
        }

        return view('frontend.' . safe_homepage_select() . '.partials.featured_products_section');
    }

    public function load_best_selling_section()
    {
        return view('frontend.' . safe_homepage_select() . '.partials.best_selling_section');
    }

    public function load_auction_products_section()
    {
        if (!addon_is_activated('auction')) {
            return;
        }
        $lang = get_system_language() ? get_system_language()->code : null;
        return view('auction.frontend.' . safe_homepage_select() . '.auction_products_section', compact('lang'));
    }

    public function load_home_categories_section()
    {
        if (get_setting('home_categories_section_status', '1') != '1') {
            return response('');
        }

        return view('frontend.' . safe_homepage_select() . '.partials.home_categories_section');
    }

    public function load_best_sellers_section(HomeLayoutService $layoutService)
    {
        $sellers = $layoutService->getRecentBestSellers();

        if ($sellers->isEmpty()) {
            return "";
        }

        return view('frontend.' . safe_homepage_select() . '.partials.best_sellers_section', compact('sellers'));
    }

    public function load_promoted_category_section()
    {
        return view('frontend.partials.promoted_category_section');
    }
    public function load_preorder_featured_products_section(HomeLayoutService $layoutService)
    {
        $preorder_products = $layoutService->getPreorderFeaturedProducts();
        return view('frontend.' . safe_homepage_select() . '.partials.preorder_products_section', compact('preorder_products'));
    }

    public function load_elite_artisans_section(HomeLayoutService $layoutService)
    {
        $elite_shops = $layoutService->getEliteArtisans();
        return view('frontend.partials.elite_artisans_section', compact('elite_shops'));
    }


    /**
     * Show the customer/seller dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $user = Auth::user();
        $redirect = $this->authService->getDashboardRedirect($user);

        if ($redirect == 'seller.dashboard') {
            return redirect()->route($redirect);
        }

        if ($user->user_type == 'delivery_boy') {
            return view('delivery_boys.dashboard');
        }

        if ($user->user_type == 'customer' || ($user->user_type == 'seller' && active_account_mode() === 'buyer')) {
            $users_cart = Cart::where('user_id', $user->id)->first();
            if ($users_cart) {
                flash(translate('You had placed your items in the shopping cart. Try to order before the product quantity runs out.'))->warning();
            }
            return view('frontend.user.customer.dashboard');
        }

        return redirect()->route('home');
    }

    public function profile(Request $request)
    {
        if (Auth::user()->user_type == 'seller' && active_account_mode() === 'seller') {
            return redirect()->route('seller.profile.index');
        } elseif (Auth::user()->user_type == 'delivery_boy') {
            return view('delivery_boys.profile');
        } else {
            return view('frontend.user.profile');
        }
    }

    public function userProfileUpdate(Request $request, \App\Services\UserService $userService)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        if ($userService->updateProfile(Auth::user(), $request->all())) {
            flash(translate('Your Profile has been updated successfully!'))->success();
        } else {
            flash(translate('Something went wrong!'))->error();
        }
        
        return back();
    }

    public function userVerifyInfoUpdate(Request $request, \App\Services\UserService $userService)
    {
        if ($userService->updateVerificationInfo(Auth::user(), $request->allFiles(), $request->all())) {
            flash(translate('Documents submitted successfully!, Please wait for verification.'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }




    public function all_categories(Request $request)
    {
        $categories = Category::with('childrenCategories')->where('parent_id', 0)->orderBy('order_level', 'desc')->get();

        return view('frontend.all_category', compact('categories'));
    }

    public function all_brands(Request $request)
    {
        $brands = Brand::all();
        return view('frontend.all_brand', compact('brands'));
    }

    public function get_category_items(Request $request)
    {
        $categories = Category::with('childrenCategories')->findOrFail($request->id);
        return view('frontend.partials.category_elements', compact('categories'));
    }

    public function premium_package_index()
    {
        $customer_packages = CustomerPackage::all();
        return view('frontend.user.customer_packages_lists', compact('customer_packages'));
    }






    public function all_coupons(Request $request)
    {
        $coupons = Coupon::where('status', 1)->where(function ($query) {
            $query->where('type', 'welcome_base')->orWhere(function ($query) {
                $query->where('type', '!=', 'welcome_base')->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')));
            });
        })->paginate(15);

        return view('frontend.coupons', compact('coupons'));
    }

    public function inhouse_products(Request $request)
    {
        $products = filter_products(Product::where('added_by', 'admin'))->with('taxes')->paginate(12)->appends(request()->query());
        return view('frontend.inhouse_products', compact('products'));
    }



    public function wallet_recharge_success()
    {
        if (Auth::user()->user_type == 'customer') {
            return view('frontend.user.customer.wallet_recharge_success');
        } elseif (Auth::user()->user_type == 'delivery_boy') {
            return view('frontend.user.customer.dashboard');
        } else {
            abort(404);
        }
    }



    


    /**
     * Show payment failed page.
     * Referenced by CmiController and other payment controllers.
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentFailed()
    {
        $error = session('payment_error', translate('Payment failed. Please try again.'));
        // Flash the error so it shows in any standard flash-message blade include
        flash($error)->error();
        return redirect()->route('checkout.shipping_info');
    }

}

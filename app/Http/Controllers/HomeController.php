<?php

namespace App\Http\Controllers;

use Auth;
use Hash;
use Cache;
use Cookie;
use App\Models\Page;
use App\Models\Shop;
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
use Carbon\Carbon;
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
    public function index()
    {
        $lang = get_system_language() ? get_system_language()->code : null;
        $featured_categories = Cache::rememberForever('featured_categories', function () {
            return Category::with('bannerImage')->where('featured', 1)->get();
        });
        $hot_categories = Cache::rememberForever('hot_categories', function () {
            return Category::with('bannerImage')->where('hot_category', '1')->get();
        });

        $authUser = Auth::user();
        if (get_setting('portfolio_landing')) {
            $goingons = Blog::where('status', 1)->where('going_on', 1)->latest()->get();
            if (!auth()->check()) {
                return view('frontend.portfolio.index', compact('lang','goingons'));
            }
            //dd($authUser->shop);
            if (($authUser->verification_status == 0) ||( $authUser->shop && $authUser->shop->verification_status == 0)) {
                return view('frontend.portfolio.index', compact('lang','goingons'));
            }
        }

        return view('frontend.' . get_setting('homepage_select') . '.index', compact('featured_categories','hot_categories', 'lang'));
    }

    public function load_todays_deal_section(HomeLayoutService $layoutService)
    {
        $todays_deal_products = $layoutService->getTodaysDealProducts();
        return view('frontend.' . get_setting('homepage_select') . '.partials.todays_deal', compact('todays_deal_products'));
    }

    public function load_newest_product_section(Request $request, HomeLayoutService $layoutService)
    {
        $newest_products = $layoutService->getNewestProducts(12, $request->page);
        return view('frontend.' . get_setting('homepage_select') . '.partials.newest_products_section', compact('newest_products'));
    }

    public function load_featured_section()
    {
        return view('frontend.' . get_setting('homepage_select') . '.partials.featured_products_section');
    }

    public function load_best_selling_section()
    {
        return view('frontend.' . get_setting('homepage_select') . '.partials.best_selling_section');
    }

    public function load_auction_products_section()
    {
        if (!addon_is_activated('auction')) {
            return;
        }
        $lang = get_system_language() ? get_system_language()->code : null;
        return view('auction.frontend.' . get_setting('homepage_select') . '.auction_products_section', compact('lang'));
    }

    public function load_home_categories_section()
    {
        return view('frontend.' . get_setting('homepage_select') . '.partials.home_categories_section');
    }

    public function load_best_sellers_section()
    {
        return view('frontend.' . get_setting('homepage_select') . '.partials.best_sellers_section');
    }

    public function load_promoted_category_section()
    {
        return view('frontend.partials.promoted_category_section');
    }
    public function load_preorder_featured_products_section(HomeLayoutService $layoutService)
    {
        $preorder_products = $layoutService->getPreorderFeaturedProducts();
        return view('frontend.' . get_setting('homepage_select') . '.partials.preorder_products_section', compact('preorder_products'));
    }

    public function load_elite_artisans_section(HomeLayoutService $layoutService)
    {
        $elite_shops = $layoutService->getEliteArtisans();
        return view('frontend.partials.elite_artisans_section', compact('elite_shops'));
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view($this->authService->getLoginView(Route::currentRouteName()));
    }


    // public function verifyRegEmailorPhone(){
    //     $type = 'customer';
    //     if (Auth::check()) {
    //         if ((Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'seller')) {
    //             flash(translate('Admin or seller cannot be a customer'))->error();
    //             return back();
    //         }
    //         if (Auth::user()->user_type == 'customer') {
    //             flash(translate('This user already a customer'))->error();
    //             return back();
    //         }
    //     } else {
    //         return view('auth.' . get_setting('authentication_layout_select') . '.customer_reg_verification', compact('type'));
    //     }
    // }

    public function registration(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $this->authService->processRegistrationReferral($request);

        $email = null;
        $phone = null;
        return view('auth.' . get_setting('authentication_layout_select') . '.user_registration', compact('email','phone'));
    }

    public function cart_login(Request $request)
    {
        if ($this->authService->authenticateUser($request->all(), $request->has('remember'))) {
            return back();
        }

        flash(translate('Invalid email or password!'))->warning();
        return back();
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

        if ($user->user_type == 'customer') {
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
        if (Auth::user()->user_type == 'seller') {
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

        // dd($categories);
        return view('frontend.all_category', compact('categories'));
    }

    public function all_brands(Request $request)
    {
        $brands = Brand::all();
        return view('frontend.all_brand', compact('brands'));
    }

    public function home_settings(Request $request)
    {
        return view('home_settings.index');
    }

    public function top_10_settings(Request $request)
    {
        foreach (Category::all() as $key => $category) {
            if (is_array($request->top_categories) && in_array($category->id, $request->top_categories)) {
                $category->top = 1;
                $category->save();
            } else {
                $category->top = 0;
                $category->save();
            }
        }

        foreach (Brand::all() as $key => $brand) {
            if (is_array($request->top_brands) && in_array($brand->id, $request->top_brands)) {
                $brand->top = 1;
                $brand->save();
            } else {
                $brand->top = 0;
                $brand->save();
            }
        }

        flash(translate('Top 10 categories and brands have been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }

    public function sellerpolicy()
    {
        $page =  Page::where('type', 'seller_policy_page')->first();
        return view("frontend.policies.sellerpolicy", compact('page'));
    }

    public function returnpolicy()
    {
        $page =  Page::where('type', 'return_policy_page')->first();
        return view("frontend.policies.returnpolicy", compact('page'));
    }

    public function supportpolicy()
    {
        $page =  Page::where('type', 'support_policy_page')->first();
        return view("frontend.policies.supportpolicy", compact('page'));
    }

    public function terms()
    {
        $page =  Page::where('type', 'terms_conditions_page')->first();
        return view("frontend.policies.terms", compact('page'));
    }

    public function privacypolicy()
    {
        $page =  Page::where('type', 'privacy_policy_page')->first();
        return view("frontend.policies.privacypolicy", compact('page'));
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


    // Ajax call
    public function new_verify(Request $request)
    {
        $email = $request->email;
        if (isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = translate('Email already exists!');
            return json_encode($response);
        }

        $response = $this->send_email_change_verification_mail($request, $email);
        return json_encode($response);
    }


    // Form request
    public function update_email(Request $request)
    {
        $email = $request->email;
        if (isUnique($email)) {
            $customerVerification = RegistrationVerificationCode::where('code', $request->code);
            $customerVerification = $customerVerification->where('email', $email);
            $customerVerification = $customerVerification->first();
            if ($customerVerification == null) {
                flash(translate('Verification code do not matched'))->error();
                return back();
            } else {
                $this->send_email_change_verification_mail($request, $email);
                flash(translate('A verification mail has been sent to the new email address you provided.'))->success();
                return back();
            }
        }

        flash(translate('Email already exists!'))->warning();
        return back();
    }

    public function send_email_change_verification_mail($request, $email)
    {
        $user = auth()->user();
        $response['status'] = 0;
        $response['message'] = 'Unknown';
        try {
            EmailUtility::change_email_verification($user, $user->user_type, $email);
            $response['status'] = 1;
            $response['message'] = translate("A verification mail has been sent to your new mail you provided us with.");
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function email_change_callback(Request $request)
    {
        if ($request->has('new_email_verificiation_code') && $request->has('email')) {
            $verification_code_of_url_param =  $request->input('new_email_verificiation_code');
            $user = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if ($user != null) {

                $user->email = $request->input('email');
                $user->new_email_verificiation_code = null;
                $user->save();

                auth()->login($user, true);

                flash(translate('Email Changed successfully'))->success();
                if ($user->user_type == 'seller') {
                    return redirect()->route('seller.dashboard');
                }
                return redirect()->route('dashboard');
            }
        }

        flash(translate('Email was not verified. Please resend your mail!'))->error();
        return redirect()->route('dashboard');
    }

    public function reset_password_with_code(Request $request)
    {
        if (($user = User::where('email', $request->email)->where('verification_code', $request->code)->first()) != null) {
            if ($request->password == $request->password_confirmation) {
                $user->password = Hash::make($request->password);
                $user->email_verified_at = date('Y-m-d h:m:s');
                $user->save();
                event(new PasswordReset($user));
                auth()->login($user, true);

                flash(translate('Password updated successfully'))->success();

                if (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff') {
                    return redirect()->route('admin.dashboard');
                }
                return redirect()->route('home');
            } else {
                flash(translate("Password and confirm password didn't match"))->warning();
                $email = $user->email;
                return view('auth.'.get_setting('authentication_layout_select').'.reset_password', compact('email'));
            }
        } else {
            flash(translate("Verification code mismatch"))->error();
            $email = $request->email;
            return view('auth.'.get_setting('authentication_layout_select').'.reset_password', compact('email'));
        }
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

    public function import_data(Request $request)
    {
        $upload_path = $request->file('uploaded_file')->store('uploads', 'local');
        $sql_path = $request->file('sql_file')->store('uploads', 'local');

        $zip = new ZipArchive;
        $zip->open(base_path('public/'.$upload_path));
        $zip->extractTo('public/uploads/all');

        $zip1 = new ZipArchive;
        $zip1->open(base_path('public/'.$sql_path));
        $zip1->extractTo('public/uploads');

        Artisan::call('cache:clear');
        $sql_path = base_path('public/uploads/demo_data.sql');
        DB::unprepared(file_get_contents($sql_path));
    }

    public function sendRegVerificationCode(Request $request, \App\Services\UserService $userService)
    {
         $request->validate([
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_register') == 1, ['required', new Recaptcha()], ['sometimes'])
            ],
        ]);

        $email = $request->email ?? null;
        $phone = $request->phone;

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (User::where('email', $email)->first() != null) {                
                return response()->json(['status' => 0, 'message' => translate('Email already exists.')]);
            }
        } elseif ($phone) {
            $formattedPhone = '+' . $request->country_code . preg_replace('/\D+/', '', $phone);
            if (User::where('phone', $formattedPhone)->first() != null) {
                return response()->json(['status' => 0, 'message' => translate('Phone already exists.')]);
            }
        }

        if ($userService->sendRegistrationCode($email, $phone, $request->country_code)) {
            return response()->json(['status' => 1, 'message' => translate('Verification code sent successfully.')]);
        } else {
            return response()->json(['status' => 0, 'message' => translate('Verification code sending failed.')]);
        }
    }

    public function regVerifyCode($id)
    {
        // $customerVerification = $id;
        $customerVerification = RegistrationVerificationCode::whereId(decrypt($id))->first();
        return view('auth.' . get_setting('authentication_layout_select') . '.customer_verify_confirmation', compact('customerVerification'));
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

    public function regVerifyCodeConfirmation(Request $request, \App\Services\UserService $userService)
    {
        $email = $request->email ?? null;
        $phone = $request->phone ? '+' . $request->country_code . $request->phone : null;

        if ($userService->verifyRegistrationCode($request->verification_code, $email, $phone)) {
            return response()->json(['status' => 1, 'message' => translate('Verification Successful')]);
        } else {
            return response()->json(['status' => 0, 'message' => translate('Verification Code did not match')]);
        }
    }

    public function sendEmailUpdateVerificationCode(Request $request)
    {
        $user = auth()->user();
        $phone = $request->phone != null ? '+' . $request->country_code . $request->phone : null;
        $email = $request->email;
        if (isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = translate('Email already exists!');
            return json_encode($response);
        }

        $verificationCode = rand(100000, 999999);
        $customerVerification = RegistrationVerificationCode::updateOrCreate(
            ['email' => $email, 'phone' => $phone],
            ['code' => $verificationCode]
        );

        try {
            EmailUtility::email_otp_verification_for_update_email($user, $user->user_type, $verificationCode, $email);
            $response['status'] = 1;
            $response['message'] = translate("We've sent a verification code to your previous email address.");
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }
        return json_encode($response);



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

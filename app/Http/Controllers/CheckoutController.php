<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Address;
use App\Models\Carrier;
use App\Models\CombinedOrder;
use App\Models\Country;
use App\Models\Product;
use App\Models\User;
use App\Utility\EmailUtility;
use App\Utility\NotificationUtility;
use App\Models\City;
use App\Models\State;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use App\Models\PaymentToken;

class CheckoutController extends Controller
{

    public function __construct()
    {
        //
    }

    public function index(Request $request)
    {
        if(get_setting('guest_checkout_activation') == 0 && !Auth::check()){
            return Redirect::route('user.login');
        }

        if(Auth::check() && !$request->user()->hasVerifiedEmail()){
            return Redirect::route('verification.notice');
        }

        $country_id = 0;
        $city_id = 0;
        $area_id=0;
        $address_id = 0;
        $shipping_info = array();

        if (Auth::check()) {
            $user_id = Auth::id();
            $carts = Cart::query()->where('user_id', $user_id)->active()->get();
            $addresses = Address::query()->where('user_id', $user_id)->get();
            if(count($addresses)){
                $address = $addresses->toQuery()->first();
                $address_id = $address->id;
                $country_id = $address->country_id;
                $city_id = $address->city_id;
                $area_id = $address->area_id;
                $default_address =$addresses->toQuery()->where('set_default', 1)->first();
                if($default_address != null){
                    $address_id = $default_address->id;
                    $country_id = $default_address->country_id;
                    $city_id = $default_address->city_id;
                    $area_id = $default_address->area_id;
                }
            }
        }
        else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = ($temp_user_id != null) ? Cart::query()->where('temp_user_id', $temp_user_id)->active()->get() : [];
        }

        $shipping_info['country_id'] = $country_id;
        $shipping_info['city_id'] = $city_id;
        $shipping_info['area_id'] = $area_id;
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;
        $default_carrier_id = null;
        $default_shipping_type = 'home_delivery';

        if ($carts && count($carts) > 0) {
            $carts->toQuery()->update(['address_id' => $address_id]);
            $carts = $carts->fresh();

            $carrier_list = array();
            if (get_setting('shipping_type') == 'carrier_wise_shipping') {
                $default_shipping_type = 'carrier';
               // $zone = $country_id != 0 ? Country::where('id', $country_id)->first()->zone_id : 0;
               $zone = $country_id != 0 ? Country::query()->where('id', $country_id)->where('status', 1)->first()->zone_id ?? 0 : 0;

                $carrier_query = Carrier::query()->where('status', 1);
                $carrier_query->whereIn('id',function ($query) use ($zone) {
                    $query->select('carrier_id')->from('carrier_range_prices')
                        ->where('zone_id', $zone);
                })->orWhere('free_shipping', 1);
                $carrier_list = $carrier_query->get();

                if (count($carrier_list) > 0) {
                    $default_carrier_id = $carrier_list->toQuery()->first()->id;
                }
            }

            foreach ($carts as $key => $cartItem) {
                $product = Product::query()->find($cartItem['product_id']);
                $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];

                if (get_setting('shipping_type') == 'carrier_wise_shipping') {
                    $cartItem['shipping_cost'] = $country_id != 0 ? getShippingCost($carts, $key, $shipping_info, $default_carrier_id) : 0;
                } else {
                    $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info);
                }
                $cartItem['shipping_type'] = $default_shipping_type;
                $cartItem['carrier_id'] = $default_carrier_id;
                $shipping += $cartItem['shipping_cost'];
                $cartItem->save();
            }
            $total = $subtotal + $tax + $shipping;

            $carts = $carts->fresh();

            return View::make('frontend.checkout', compact('carts', 'address_id', 'total', 'carrier_list', 'shipping_info'));
        }
        Session::flash('error', translate('Please Select cart items to Proceed'));
        return Redirect::back();
    }

    //check the selected payment gateway and redirect to that controller accordingly
    public function checkout(Request $request)
    {
        // if guest checkout, create user
        if(auth()->user() == null){
            $guest_user = $this->createUser($request->except('_token', 'payment_option'));
            if(gettype($guest_user) == "object"){
                $errors = $guest_user;
                if ($request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => translate('Validation failed'), 'errors' => $errors], 422);
                }
                return Redirect::route('checkout.shipping_info')->withErrors($errors);
            }

            if($guest_user == 0){
                Session::flash('warning', translate('Please try again later.'));
                return Redirect::route('checkout.shipping_info');
            }
        }

        if ($request->input('payment_option') == null) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => translate('There is no payment option is selected.')], 422);
            }
            Session::flash('warning', translate('There is no payment option is selected.'));
            return Redirect::route('checkout.shipping_info');
        }
        $user = Auth::user();
        $carts = Cart::query()->where('user_id', $user->getKey())->active()->get();


        // Minumum order amount check
        if(get_setting('minimum_order_amount_check') == 1){
            $subtotal = 0;
            foreach ($carts as $key => $cartItem){
                $product = Product::query()->find($cartItem['product_id']);
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
            }
            if ($subtotal < get_setting('minimum_order_amount')) {
                if ($request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => translate('You order amount is less than the minimum order amount')], 422);
                }
                Session::flash('warning', translate('You order amount is less than the minimum order amount'));
                return Redirect::route('home');
            }
        }
        // Minumum order amount check end

        (new OrderController)->store($request);

        if(count($carts) > 0){
            $carts->toQuery()->delete();
        }

        $request->session()->put('payment_type', 'cart_payment');

        $data['combined_order_id'] = $request->session()->get('combined_order_id');
        $data['payment_method'] = $request->input('payment_option');
        $data['save_card'] = $request->input('save_card', 0);
        $request->session()->put('payment_data', $data);
        if ($request->session()->get('combined_order_id') != null) {
            // Intercept insecure gateways and redirect to placeholder
            $insecureGateways = ['sslcommerz', 'aamarpay', 'payfast'];
            if (in_array($request->input('payment_option'), $insecureGateways)) {
                return (new \App\Services\SecurePaymentPlaceholder)->pay($request);
            }

            // If block for Online payment, wallet and cash on delivery. Else block for Offline payment
            $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->input('payment_option')))) . "Controller";
            if (class_exists($decorator)) {
                $payment_controller = new $decorator;
                if (!method_exists($payment_controller, 'pay')) {
                    \Illuminate\Support\Facades\Log::error("Payment controller 'pay' method missing for method: " . $request->input('payment_option'));
                    Session::flash('error', translate('Selected payment method is currently unavailable.'));
                    return Redirect::route('checkout.shipping_info');
                }

                if ($request->ajax()) {
                    // Start the payment flow but we need the redirect URL or the form HTML
                    $response = $payment_controller->pay($request);
                    
                    if ($response instanceof \Illuminate\Http\RedirectResponse) {
                        return response()->json(['status' => 'success', 'type' => 'redirect', 'url' => $response->getTargetUrl()]);
                    } elseif ($response instanceof \Illuminate\View\View) {
                        return response()->json(['status' => 'success', 'type' => 'html', 'html' => $response->render()]);
                    }
                    return $response;
                }
                return $payment_controller->pay($request);
            }
            else {
                $combined_order = CombinedOrder::query()->findOrFail($request->session()->get('combined_order_id'));
                $manual_payment_data = array(
                    'name'   => $request->input('payment_option'),
                    'amount' => $combined_order->grand_total,
                    'trx_id' => $request->input('trx_id'),
                    'photo'  => $request->input('photo')
                );
                foreach ($combined_order->orders as $order) {
                    $order->manual_payment = 1;
                    $order->manual_payment_data = json_encode($manual_payment_data);
                    $order->save();
                }
                if ($request->ajax()) {
                    return response()->json(['status' => 'success', 'type' => 'redirect', 'url' => route('order_confirmed')]);
                }
                Session::flash('success', translate('Order placed successfully'));
                return Redirect::route('order_confirmed');
            }
        }
    }

    public function createUser($guest_shipping_info)
    {
        $validator = Validator::make($guest_shipping_info, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'phone' => 'required|max:12',
            'address' => 'required|max:255',
            'country_id' => 'required|Integer',
            'state_id' => get_setting('has_state') == 1 ? 'required|integer' : 'nullable|integer',
            'city_id' => 'required|Integer',
            'area_id'  => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $success = 1;
        $password = substr(hash('sha512', rand()), 0, 8);
        $isEmailVerificationEnabled = get_setting('email_verification');

        // User Create
        $user = new User();
        $user->name = $guest_shipping_info['name'];
        $user->email = $guest_shipping_info['email'];
        $user->phone = addon_is_activated('otp_system') ? '+'.$guest_shipping_info['country_code'].$guest_shipping_info['phone'] : null;
        $user->password = Hash::make($password);
        $user->email_verified_at = $isEmailVerificationEnabled != 1 ? date('Y-m-d H:m:s') : null;
        $user->save();

        // Guest Account Opening and verification(if activated) eamil send
        try {
            EmailUtility::customer_registration_email('registration_from_system_email_to_customer', $user, $password);
        } catch (\Exception $e) {
            $success = 0;
            $user->delete();
        }

        if($success == 0){
            return $success;
        }

        // Sending email verification Notification
        if($isEmailVerificationEnabled == 1){
            EmailUtility::email_verification($user, 'customer');
        }

        // Customer Account Opening Email to Admin
        if ((get_email_template_data('customer_reg_email_to_admin', 'status') == 1)) {
            try {
                EmailUtility::customer_registration_email('customer_reg_email_to_admin', $user, null);
            } catch (\Exception $e) {}
        }

        // User Address Create
        $sameAsShipping   = ($guest_shipping_info['same_as_shipping'] ?? 0) == 1;
        $address = new Address;
        $address->user_id       = $user->getKey();
        $address->address       = $guest_shipping_info['address'];
        $address->country_id    = $guest_shipping_info['country_id'];
        $address->state_id      = $guest_shipping_info['state_id'] ?? null;
        $address->city_id       = $guest_shipping_info['city_id'];
        $address->postal_code   = $guest_shipping_info['postal_code'];
        $address->area_id       = $guest_shipping_info['area_id'] ?? null;
        $address->phone         = '+'.$guest_shipping_info['country_code'].$guest_shipping_info['phone'];
        $address->longitude     = isset($guest_shipping_info['longitude']) ? $guest_shipping_info['longitude'] : null;
        $address->latitude      = isset($guest_shipping_info['latitude']) ? $guest_shipping_info['latitude'] : null;
        if (!get_setting('billing_address_required') || $sameAsShipping) {
            $address->set_billing = 1;
        }
        $address->save();
        $address_billing_id=$address->id; 

        //user billing Address
        if(get_setting('billing_address_required') && !$sameAsShipping){
        $billing_address = new Address;
        $billing_address->user_id       = $user->getKey();
        $billing_address->address       = $guest_shipping_info['billing_address'];
        $billing_address->country_id    = $guest_shipping_info['billing_country_id'];
        $billing_address->state_id      = $guest_shipping_info['billing_state_id'] ?? null;
        $billing_address->city_id       = $guest_shipping_info['billing_city_id'];
        $billing_address->postal_code   = $guest_shipping_info['billing_postal_code'];
        $billing_address->area_id       = $guest_shipping_info['billing_area_id'] ?? null;
        $billing_address->phone         = $guest_shipping_info['billing_phone'];
        $address->set_billing           = 1;
        $billing_address->save();
        $address_billing_id=$billing_address->id;

        }
        

        $carts = Cart::where('temp_user_id', Session::get('temp_user_id'))->get();
        $carts->toQuery()->update([
                'user_id' => $user->getKey(),
                'temp_user_id' => null
            ]);
        $carts->toQuery()->active()->update([
                'address_id' => $address->id,
                'billing_address' => $address_billing_id
            ]);

        Auth::login($user);

        Session::forget('temp_user_id');
        Session::forget('guest_shipping_info');

        return $success;
    }

    //redirects to this method after a successfull checkout
    public function checkout_done1($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();

            // Order paid notification to Customer, Seller, & Admin
            EmailUtility::order_email($order, 'paid'); 
            
            // Calculate Commission from seller, Customer Affiliate earning and Customers Club Point
            calculateCommissionAffilationClubPoint($order);
        }
        Session::put('combined_order_id', $combined_order_id);
    }
    
    //redirects to this method after a successfull checkout
    public function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $order) {
            try {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'paid';
                $order->payment_details = $payment;
                $order->save();

                // Order paid notification to Customer, Seller, & Admin
                EmailUtility::order_email($order, 'paid'); 
                
                // Calculate Commission from seller, Customer Affiliate earning and Customers Club Point
                calculateCommissionAffilationClubPoint($order);
            } catch (\Exception $e) {
                \Log::error("Error in checkout_done loop for order id {$order->id}: " . $e->getMessage());
            }
        }
        Session::put('combined_order_id', $combined_order_id);
        
        if (request()->ajax()) {
            return response()->json(['status' => 'success', 'type' => 'redirect', 'url' => route('order_confirmed')]);
        }
        
        return redirect()->route('order_confirmed');
    }

    // ================ Will not use after single page checkout ========[start]
    public function get_shipping_info(Request $request)
    {
        if(get_setting('guest_checkout_activation') == 0 && !Auth::check()){
            return Redirect::route('user.login');
        }

        if (Auth::check()) {
            $user_id = Auth::id();
            $carts = Cart::where('user_id', $user_id)->get();
        }
        else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
        }
        if ($carts && count($carts) > 0) {
            $categories = Category::all();
            return View::make('frontend.shipping_info', compact('categories', 'carts'));
        }
        Session::flash('success', translate('Your cart is empty'));
        return Redirect::back();
    }

    public function store_shipping_info(Request $request)
    {
        $auth_user = Auth::user();
        $temp_user_id = $request->session()->has('temp_user_id') ? $request->session()->get('temp_user_id') : null;

        if(!Auth::check() && get_setting('guest_checkout_activation') == 0){
            return Redirect::route('user.login');
        }

        if(Auth::check()){
            if($request->input('address_id') == null){
                Session::flash('warning', translate("Please add shipping address"));
                return Redirect::route('checkout.shipping_info');
            }

            $carts = Cart::where('user_id', Auth::id())->get();
            foreach ($carts as $key => $cartItem) {
                $cartItem->address_id = $request->input('address_id');
                $cartItem->save();
            }
        }
        else{
            if(get_setting('guest_checkout_activation') == 1){
                if($request->input('name') == null || $request->input('email') == null || $request->input('address') == null ||
                    $request->input('country_id') == null || $request->input('state_id') == null || $request->input('city_id') == null ||
                        $request->input('postal_code') == null || $request->input('phone') == null) {
                    Session::flash('warning', translate("Please add shipping address"));
                    return Redirect::route('checkout.shipping_info');
                }
                $shipping_info['name'] = $request->input('name');
                $shipping_info['email'] = $request->input('email');
                $shipping_info['address'] = $request->input('address');
                $shipping_info['country_id'] = $request->input('country_id');
                $shipping_info['state_id'] = $request->input('state_id');
                $shipping_info['city_id'] = $request->input('city_id');
                $shipping_info['area_id'] = $request->input('area_id');
                $shipping_info['postal_code'] = $request->input('postal_code');
                $shipping_info['phone'] = '+'.$request->input('country_code').$request->input('phone');
                $shipping_info['longitude'] = $request->input('longitude');
                $shipping_info['latitude'] = $request->input('latitude');
                $request->session()->put('guest_shipping_info', $shipping_info);
            }
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
        }

        if ($carts->isEmpty()) {
            Session::flash('warning', translate('Your cart is empty'));
            return Redirect::route('home');
        }

        $deliveryInfo = [];

        // Logged In User Delivery info
        if($auth_user != null){
            $address = Address::where('id', $carts[0]['address_id'])->first();
            $deliveryInfo['country_id'] = $address->country_id;
            $deliveryInfo['city_id'] = $address->city_id;
            $deliveryInfo['area_id'] = $address->area_id;
        }

        // Guest User Delivery info
        elseif($temp_user_id != null){
            $deliveryInfo['country_id'] = $request->input('country_id');
            $deliveryInfo['city_id'] = $request->input('city_id');
            $deliveryInfo['area_id'] = $request->input('area_id');
        }

        $carrier_list = array();
        if (get_setting('shipping_type') == 'carrier_wise_shipping') {
            $country_id = $auth_user != null ? $carts[0]['address']['country_id'] : $request->input('country_id');
            $zone = Country::where('id', $country_id)->first()->zone_id;

            $carrier_query = Carrier::where('status', 1);
            $carrier_query->whereIn('id',function ($query) use ($zone) {
                $query->select('carrier_id')->from('carrier_range_prices')
                    ->where('zone_id', $zone);
            })->orWhere('free_shipping', 1);
            $carrier_list = $carrier_query->get();
        }

        return view('frontend.delivery_info', compact('carts', 'carrier_list', 'deliveryInfo'));
    }

    public function store_delivery_info(Request $request)
    {
        $authUser = Auth::user();
        $tempUser = $request->session()->has('temp_user_id') ? $request->session()->get('temp_user_id') : null;
        $carts = Auth::check() ?
                Cart::where('user_id', Auth::id())->get() :
                ($tempUser != null ? Cart::where('temp_user_id', $tempUser)->get() : collect());

        if ($carts->isEmpty()) {
            Session::flash('warning', translate('Your cart is empty'));
            return Redirect::route('home');
        }

        $shipping_info = $authUser != null ? Address::where('id', $carts[0]['address_id'])->first() : null;
        $deliveryInfo = [];

        // Logged In User Delivery info
        if($authUser != null){
            $deliveryInfo['country_id'] = $shipping_info->country_id;
            $deliveryInfo['city_id'] = $shipping_info->city_id;
             $deliveryInfo['area_id'] = $shipping_info->area_id;
        }

        // Guest User Shipping info
        elseif($tempUser != null){
            $deliveryInfo['country_id'] = Session::get('guest_shipping_info')['country_id'];
            $deliveryInfo['city_id'] = Session::get('guest_shipping_info')['city_id'];
            $deliveryInfo['area_id'] = Session::get('guest_shipping_info')['area_id'];
        }

        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        if ($carts && count($carts) > 0) {
            foreach ($carts as $key => $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];

                if (get_setting('shipping_type') != 'carrier_wise_shipping' || $request->input('shipping_type_' . $product->user_id) == 'pickup_point') {
                    if ($request->input('shipping_type_' . $product->user_id) == 'pickup_point') {
                        $cartItem->shipping_type = 'pickup_point';
                        $cartItem->pickup_point = $request->input('pickup_point_id_' . $product->user_id);
                    } else {
                        $cartItem->shipping_type = 'home_delivery';
                    }
                    $cartItem->shipping_cost = 0;
                    if ($cartItem['shipping_type'] == 'home_delivery') {
                        $cartItem->shipping_cost = getShippingCost($carts, $key, $deliveryInfo);
                    }
                } else {
                    $cartItem->shipping_type = 'carrier';
                    $cartItem->carrier_id = $request->input('carrier_id_' . $product->user_id);
                    $cartItem->shipping_cost = getShippingCost($carts, $key, $deliveryInfo, $cartItem['carrier_id']);
                }

                $shipping += $cartItem['shipping_cost'];
                $cartItem->save();
            }
            $total = $subtotal + $tax + $shipping;

            $tokens = [];
            if (Auth::check()) {
                $tokens = PaymentToken::where('user_id', Auth::id())
                    ->where('is_active', true)
                    ->latest()
                    ->get();
            }

            return View::make('frontend.payment_select', compact('carts', 'shipping_info', 'total', 'tokens'));
        } else {
            Session::flash('warning', translate('Your Cart was empty'));
            return Redirect::route('home');
        }
    }
    // ================ Will not use after single page checkout ========[End]

    public function apply_coupon_code(Request $request)
    {
        $user       = Auth::user();
        $temp_user  = Session::has('temp_user_id') ? Session::get('temp_user_id') : null;
        $coupon     = Coupon::where('code', $request->input('code'))->first();
        $proceed    = $request->input('proceed');
        $response_message = array();

        // if the Coupon type is Welcome base, check the user has this coupon or not
        $canUseCoupon = true;
        if($coupon && $coupon->type == 'welcome_base'){
            if($user != null) {
                // $userCoupon = user assigned coupon
                $userCoupon = $user->userCoupon;
                if(!$userCoupon){
                    $canUseCoupon = false;
                }
            }
            else {
                $canUseCoupon = false;
            }
        }

        if ($coupon != null && $canUseCoupon) {

            //  Coupon expiry Check
            if($coupon->type != 'welcome_base') {
                $validationDateCheckCondition  = strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date;
            }
            else {
                $validationDateCheckCondition = false;
                if($userCoupon){
                    $validationDateCheckCondition  = $userCoupon->expiry_date >= strtotime(date('d-m-Y H:i:s')) ;
                }
            }
            if ($validationDateCheckCondition) {
                if (($user == null && Session::has('temp_user_id')) || CouponUsage::where('user_id', $user->getKey())->where('coupon_id', $coupon->id)->first() == null) {
                    $coupon_details = json_decode($coupon->details);

                    $user_carts = $user != null ?
                            Cart::where('user_id', $user->getKey())->where('owner_id', $coupon->user_id)->active()->get() :
                            Cart::where('owner_id', $coupon->user_id)->where('temp_user_id', $temp_user)->active()->get();

                    $coupon_discount = 0;

                    if ($coupon->type == 'cart_base' || $coupon->type == 'welcome_base') {
                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach ($user_carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                            $shipping += $cartItem['shipping_cost'];
                        }
                        $sum = $subtotal + $tax + $shipping;
                        if ($coupon->type == 'cart_base' && $sum >= $coupon_details->min_buy) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                            }
                        }
                        elseif ($coupon->type == 'welcome_base' && $sum >= $userCoupon->min_buy)  {
                            $coupon_discount  = $userCoupon->discount_type == 'percent' ?  (($sum * $userCoupon->discount) / 100) : $userCoupon->discount;
                        }
                    }
                    elseif ($coupon->type == 'product_base') {
                        foreach ($user_carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                    }
                                }
                            }
                        }
                    }

                    if ($coupon_discount > 0) {
                        $user_carts->toQuery()->update(
                            [
                                'discount' => $coupon_discount / count($user_carts),
                                'coupon_code' => $request->input('code'),
                                'coupon_applied' => 1
                            ]
                        );

                        $response_message['response'] = 'success';
                        $response_message['message'] = translate('Coupon has been applied');
                    } else {
                        $response_message['response'] = 'warning';
                        $response_message['message'] = translate('This coupon is not applicable to your cart products!');
                    }
                } else {
                    $response_message['response'] = 'warning';
                    $response_message['message'] = translate('You already used this coupon!');
                }
            } else {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('Coupon expired!');
            }
        } else {
            $response_message['response'] = 'danger';
            $response_message['message'] = translate('Invalid coupon!');
        }

        $carts = $user != null ?
                Cart::where('user_id', $user->getKey())->active()->get() :
                Cart::where('temp_user_id', $temp_user)->active()->get();
        $shipping_info = $user != null ? Address::where('id', $carts[0]['address_id'])->first() : null;

        $returnHTML = View::make('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'))->render();
        return \Illuminate\Support\Facades\Response::json(array('response_message' => $response_message, 'html' => $returnHTML));
    }

    public function remove_coupon_code(Request $request)
    {
        $user       = Auth::user();
        $temp_user  = Session::has('temp_user_id') ? Session::get('temp_user_id') : null;
        $carts      = $user != null ?
                Cart::where('user_id', $user->getKey())->active()->get() :
                Cart::where('temp_user_id', $temp_user)->active()->get();

        $carts->toQuery()->update(
            [
                'discount' => 0.00,
                'coupon_code' => '',
                'coupon_applied' => 0
            ]
        );

        $coupon = Coupon::where('code', $request->input('code'))->first();
        $carts = $carts->fresh();

        $shipping_info = $user != null ? Address::where('id', $carts[0]['address_id'])->first() : null;

        $returnHTML = View::make('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'))->render();
        return \Illuminate\Support\Facades\Response::json(array('html' => $returnHTML));
    }

    public function apply_club_point(Request $request) {
        if (addon_is_activated('club_point')){
            $point = $request->input('point');

            if(Auth::user()->point_balance >= $point) {
                $request->session()->put('club_point', $point);
                Session::flash('success', translate('Point has been redeemed'));
            }
            else {
                Session::flash('warning', translate('Invalid point!'));
            }
        }
        return Redirect::back();
    }

    public function remove_club_point(Request $request) {
        $request->session()->forget('club_point');
        return Redirect::back();
    }

    public function order_confirmed($combined_order_id = null)
    {
        if ($combined_order_id == null) {
            $combined_order_id = Session::get('combined_order_id');
        }

        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        Cart::where('user_id', $combined_order->user_id)
            ->delete();

        return View::make('frontend.order_confirmed', compact('combined_order'));
    }

    public function payment_failed($combined_order_id = null)
    {
        if ($combined_order_id == null) {
            $combined_order_id = Session::get('combined_order_id');
        }
        
        $payment_type = Session::get('payment_type');
        $payment_data = Session::get('payment_data');

        // Handle cases where there might not be an order (wallet, package)
        if (!$combined_order_id && (!$payment_type || $payment_type != 'order_re_payment' || !isset($payment_data['order_id']))) {
            if (in_array($payment_type, ['wallet_payment', 'customer_package_payment', 'seller_package_payment'])) {
                $orders = collect([]);
                $retry_url = $payment_type == 'wallet_payment'
                    ? route('wallet.index')
                    : route('customer_packages_list_show');

                return View::make('frontend.payment_failed', compact('orders', 'retry_url'));
            }
            return Redirect::route('home');
        }

        $orders = collect([]);
        if ($combined_order_id) {
            $combined_order = CombinedOrder::find($combined_order_id);
            if ($combined_order) {
                $orders = $combined_order->orders;
            }
        } elseif ($payment_type == 'order_re_payment') {
            $order = Order::find($payment_data['order_id']);
            if ($order) {
                $orders = collect([$order]);
            }
        }

        // Mark orders as cancelled and RESTOCK
        foreach ($orders as $order) {
            if ($order->payment_status != 'paid' && $order->delivery_status != 'cancelled') {
                // Restock each item before marking as cancelled
                foreach ($order->orderDetails as $orderDetail) {
                    if ($orderDetail->delivery_status != 'cancelled') {
                        product_restock($orderDetail);
                        $orderDetail->delivery_status = 'cancelled';
                        $orderDetail->save();
                    }
                }
                
                $order->delivery_status = 'cancelled';
                $order->save();
            }
        }

        // Determine retry URL
        $retry_url = route('checkout.shipping_info');
        if ($payment_type == 'wallet_payment') {
            $retry_url = route('wallet.index');
        } elseif ($payment_type == 'customer_package_payment' || $payment_type == 'seller_package_payment') {
            $retry_url = route('customer_packages_list_show');
        }

        return View::make('frontend.payment_failed', compact('orders', 'retry_url'));
    }

    public function guestCustomerInfoCheck(Request $request){
        $user = addon_is_activated('otp_system') ?
                User::where('email', $request->input('email'))->orWhere('phone','+'.$request->input('phone'))->first() :
                User::where('email', $request->input('email'))->first();
        return ($user != null) ? true : false;
    }

    public function updateDeliveryAddress(Request $request)
    {
        $proceed = 0;
        $default_carrier_id = null;
        $default_shipping_type = 'home_delivery';
        $user = Auth::user();
        $shipping_info = array();

        $carts = $user != null ?
                Cart::where('user_id', $user->getKey())->active()->get() :
                Cart::where('temp_user_id', $request->session()->get('temp_user_id'))->active()->get();

        $carts->toQuery()->update(['address_id' => $request->input('address_id')]);

        $country_id = $user != null ?
                    Address::findOrFail($request->input('address_id'))->country_id :
                    $request->input('address_id');
        $city_id = $user != null ?
                    Address::findOrFail($request->input('address_id'))->city_id :
                    $request->input('city_id');
        $area_id = $user != null ?
                    Address::findOrFail($request->input('address_id'))->area_id :
                    $request->input('area_id');

                    
        $shipping_info['country_id'] = $country_id;
        $shipping_info['city_id'] = $city_id;
        $shipping_info['area_id'] = $area_id;
        $carrier_list = array();
        if (get_setting('shipping_type') == 'carrier_wise_shipping') {
            $default_shipping_type = 'carrier';
            //$zone = Country::where('id', $country_id)->first()->zone_id;
            $zone = $country_id != 0 ? Country::where('id', $country_id)->where('status', 1)->first()?->zone_id ?? 0 : 0;

            $carrier_query = Carrier::where('status', 1);
            $carrier_query->whereIn('id',function ($query) use ($zone) {
                $query->select('carrier_id')->from('carrier_range_prices')
                    ->where('zone_id', $zone);
            })->orWhere('free_shipping', 1);
            $carrier_list = $carrier_query->get();

            if (count($carrier_list) > 1) {
                $default_carrier_id = $carrier_list->toQuery()->first()->id;
            }
        }

        $carts = $carts->fresh();

        foreach ($carts as $key => $cartItem) {
            if (get_setting('shipping_type') == 'carrier_wise_shipping') {
                $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info, $default_carrier_id);
            } else {
                $cartItem['shipping_cost'] = getShippingCost($carts, $key, $shipping_info);
            }
            $cartItem['address_id'] = $user != null ? $request->input('address_id') : 0;
            $cartItem['shipping_type'] = $default_shipping_type;
            $cartItem['carrier_id'] = $default_carrier_id;
            $cartItem->save();
        }

        $carts = $carts->fresh();

        return array(
            'delivery_info' => View::make('frontend.partials.cart.delivery_info', compact('carts', 'carrier_list', 'shipping_info'))->render(),
            'cart_summary' => View::make('frontend.partials.cart.cart_summary', compact('carts', 'proceed'))->render(),
            'carrier_count' => count($carrier_list)
        );
    }

    public function updateBillingAddress(Request $request)
    {
        $user = Auth::user();

        $carts = $user != null ?
                Cart::query()->where('user_id', $user->getKey())->active()->get() :
                Cart::query()->where('temp_user_id', $request->session()->get('temp_user_id'))->active()->get();
        $carts->toQuery()->update(['billing_address' => $request->input('billing_address_id')]);
        $carts = $carts->fresh();
    }

    public function updateDeliveryInfo(Request $request)
    {
        $proceed = 0;
        $user = Auth::user();
        $shipping_info = array();

        if ($user != null) {
            $carts = Cart::query()->where('user_id', $user->getKey())->active()->get();
        }
        else {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = ($temp_user_id != null) ? Cart::query()->where('temp_user_id', $temp_user_id)->active()->get() : [];
        }

        $user_carts = $carts->toQuery()->where('owner_id', $request->input('user_id'))->get();

        $country_id = $user != null ?
                    Address::query()->findOrFail($carts[0]->address_id)->country_id : $request->input('country_id');
        $city_id = $user != null ?
                    Address::query()->findOrFail($carts[0]->address_id)->city_id : $request->input('city_id');
        $area_id = $user != null ?
                    Address::query()->findOrFail($carts[0]->address_id)->area_id : $request->input('area_id');
        $shipping_info['country_id'] = $country_id;
        $shipping_info['city_id'] = $city_id;
        $shipping_info['area_id'] = $area_id;

        $carts = $carts->fresh();

        $returnHTML = View::make('frontend.partials.cart_summary', compact('carts', 'shipping_info'))->render();
        return array('html' => $returnHTML);
    }

    public function orderRePayment(Request $request){
        $order = Order::findOrFail($request->input('order_id'));
        if($order != null){
            $request->session()->put('payment_type', 'order_re_payment');
            $data['order_id'] = $order->id;
            $data['payment_method'] = $request->input('payment_option');
            $request->session()->put('payment_data', $data);

            // If block for Online payment, wallet and cash on delivery. Else block for Offline payment
            $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->input('payment_option')))) . "Controller";
            if (class_exists($decorator)) {
                return (new $decorator)->pay($request);
            }
            else {
                $manual_payment_data = array(
                    'name'   => $request->input('payment_option'),
                    'amount' => $order->grand_total,
                    'trx_id' => $request->input('trx_id'),
                    'photo'  => $request->input('photo')
                );

                $order->payment_type = $request->input('payment_option');
                $order->manual_payment = 1;
                $order->manual_payment_data = json_encode($manual_payment_data);
                $order->save();

                Session::flash('success', translate('Payment done.'));
                return Redirect::route('purchase_history.details', encrypt($order->id));
            }
        }
        Session::flash('warning', translate('Order Not Found'));
        return Redirect::back();
    }

    public function orderRePaymentDone($payment_data, $payment_details = null)
    {
        $order = Order::findOrFail($payment_data['order_id']);
        $order->payment_status = 'paid';
        $order->payment_details = $payment_details;
        $order->payment_type = $payment_data['payment_method'];
        $order->save();
        (new CommissionController)->calculateCommission($order);
        (new AffiliateController)->processAffiliatePoints($order);
        (new ClubPointController)->processClubPoints($order);

        if($order->notified == 0){
            NotificationUtility::sendOrderPlacedNotification($order);
            $order->notified = 1;
            $order->save();
        }

        Session::forget('payment_type');
        Session::forget('order_id');

        Session::flash('success', translate('Payment done.'));
        return Redirect::route('purchase_history.details', encrypt($order->id));
    }

    public function fast_purchase(Request $request)
    {
        if (!\App\Services\PaymentVaultService::isEligible()) {
            Session::flash('warning', translate('You are not eligible for 1-click purchase yet.'));
            return Redirect::back();
        }

        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->where('set_default', 1)->first();
        $payment_option = \App\Services\PaymentVaultService::getPreferredPaymentMethod();

        $request->merge([
            'payment_option' => $payment_option,
            'address_id' => $address->id
        ]);

        // Reuse the existing checkout logic by simulating a full request
        return $this->checkout($request);
    }


}

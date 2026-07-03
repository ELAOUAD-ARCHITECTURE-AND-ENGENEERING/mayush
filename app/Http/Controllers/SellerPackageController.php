<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerPackage;
use Auth;
use Session;

class SellerPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seller_packages = SellerPackage::all();
        return view('frontend.seller.seller_packages_list', compact('seller_packages'));
    }

    public function purchase_package(Request $request)
    {
        $data['seller_package_id'] = $request->seller_package_id;
        $data['payment_method'] = $request->payment_option;

        $request->session()->put('payment_type', 'seller_package_payment');
        $request->session()->put('payment_data', $data);

        $seller_package = SellerPackage::findOrFail($request->seller_package_id);

        if ($seller_package->amount == 0) {
            seller_purchase_payment_done(Auth::user()->id, $request->seller_package_id, 'Free Package', null);
            flash(translate('Package purchasing successful'))->success();
            return redirect()->route('dashboard');
        }

        $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
        if (class_exists($decorator)) {
            return app($decorator)->pay($request);
        }
        
        flash(translate('Unknown payment method'))->error();
        return back();
    }

    public function purchase_payment_done($payment_data, $payment = null)
    {
        $seller_package_id = $payment_data['seller_package_id'];
        $payment_method = $payment_data['payment_method'];
        
        seller_purchase_payment_done(Auth::user()->id, $seller_package_id, $payment_method, $payment);

        flash(translate('Package purchasing successful'))->success();
        return redirect()->route('dashboard');
    }
    
    public function unpublish_products()
    {
        // Placeholder for unpublish products logic
        return redirect()->route('dashboard');
    }
}

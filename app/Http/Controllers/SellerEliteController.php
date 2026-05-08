<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EliteSubscription;
use App\Models\ElitePackage;
use App\Models\Shop;
use Auth;

class SellerEliteController extends Controller
{
    public function index() 
    { 
        return view('seller.elite.index'); 
    }

    public function pricing() 
    { 
        return view('seller.elite.pricing'); 
    }

    public function recap(Request $request) 
    { 
        $billing_cycle = $request->billing_cycle;
        return view('seller.elite.recap', compact('billing_cycle')); 
    }

    public function processPayment(Request $request) 
    { 
        $shop = Auth::user()->shop;
        if (!$shop) {
            flash(translate('Shop not found.'))->error();
            return back();
        }

        // Create a temporary subscription record
        $sub = new EliteSubscription;
        $sub->shop_id = $shop->id;
        $sub->billing_cycle = $request->billing_cycle ?? 'monthly';
        $sub->status = 'pending';
        $sub->amount_paid = 100; // Stub amount
        $sub->save();

        // Put in session for CMI controller
        session()->put('payment_type', 'elite_payment');
        session()->put('payment_data', [
            'subscription_id' => $sub->id,
            'amount' => $sub->amount_paid
        ]);

        return redirect()->route('cmi.pay');
    }

    public function paymentSuccess() 
    { 
        flash(translate('Subscription successful.'))->success();
        return redirect()->route('seller.elite.index'); 
    }

    public function paymentFail() 
    { 
        flash(translate('Subscription failed.'))->error();
        return redirect()->route('seller.elite.pricing'); 
    }

    public function cancel() 
    { 
        return redirect()->route('seller.elite.index'); 
    }

    public function updateProfile(Request $request) 
    { 
        return back(); 
    }
}

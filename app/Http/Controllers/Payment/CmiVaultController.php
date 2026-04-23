<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentToken;
use App\Services\PaymentVaultService;
use Auth;
use Session;

class CmiVaultController extends Controller
{
    public function pay(Request $request)
    {
        $tokenId = $request->input('payment_token_id');
        $token = PaymentToken::where('id', $tokenId)
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (!$token) {
            flash(translate('Invalid payment token selected.'))->error();
            return redirect()->route('checkout.payment_info');
        }

        $combined_order_id = Session::get('combined_order_id');
        
        return app(CmiController::class)->expressCharge($combined_order_id, $token);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentToken;
use Auth;

class PaymentTokenController extends Controller
{
    /**
     * Display a listing of the user's payment tokens.
     */
    public function index()
    {
        $tokens = PaymentToken::where('user_id', Auth::id())
            ->where('gateway', 'cmi')
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('frontend.user.payment_tokens', compact('tokens'));
    }

    /**
     * Set a token as the default for Express Buy.
     */
    public function setDefault(PaymentToken $token)
    {
        if ($token->user_id != Auth::id()) {
            abort(403);
        }

        // Unset old defaults
        PaymentToken::where('user_id', Auth::id())
            ->update(['is_default' => false]);

        $token->is_default = true;
        $token->save();

        flash(translate('Default payment method updated successfully.'))->success();
        return back();
    }

    /**
     * Remove the specified token (soft deactivate).
     */
    public function destroy(PaymentToken $token)
    {
        if ($token->user_id != Auth::id()) {
            abort(403);
        }

        $token->is_active = false;
        $token->is_default = false;
        $token->save();

        flash(translate('Payment method removed successfully.'))->success();
        return back();
    }
}

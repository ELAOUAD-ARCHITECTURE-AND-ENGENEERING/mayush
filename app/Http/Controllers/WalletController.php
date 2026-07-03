<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Utility\EmailUtility;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Session;

class WalletController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_offline_wallet_recharges'])->only('offline_recharge_request');
    }

    public function index()
    {
        $wallets = Wallet::where('user_id', Auth::user()->id)->latest()->paginate(10);
        return view('frontend.user.wallet.index', compact('wallets'));
    }

    public function recharge(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_option' => ['required', 'string', 'max:100'],
        ]);

        $data['amount'] = $validated['amount'];
        $data['payment_method'] = $validated['payment_option'];

        $request->session()->put('payment_type', 'wallet_payment');
        $request->session()->put('payment_data', $data);

        $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
        if (class_exists($decorator)) {
            $payment_controller = app($decorator);
            if (method_exists($payment_controller, 'pay')) {
                return $payment_controller->pay($request);
            } else {
                \Illuminate\Support\Facades\Log::error("Payment controller 'pay' method missing for method: " . $request->payment_option);
            }
        } else {
            \Illuminate\Support\Facades\Log::error("Payment controller class not found for method: " . $request->payment_option);
        }
        
        flash(translate('Selected payment method is currently unavailable. Please contact support.'))->error();
        return back();
    }

    public function wallet_payment_done($payment_data, $payment_details)
    {
        $user = Auth::user();
        app(WalletService::class)->credit($user, $payment_data['amount'], $payment_data['payment_method'], $payment_details);

        // customer Account Opening Email to Admin
        if ($this->walletRechargeEmailEnabled($user)) {
            try {
                EmailUtility::wallet_recharge_email('wallet_recharge_email_to_customer', $user, $payment_data['amount'], $payment_data['payment_method']);
            } catch (\Exception $e) {}
        }

        Session::forget('payment_data');
        Session::forget('payment_type');

        flash(translate('Recharge completed'))->success();
        return redirect()->route('wallet.index');
    }

    public function wallet_payment_done1($payment_data, $payment_details)
    {
        $user = Auth::user();
        app(WalletService::class)->credit($user, $payment_data['amount'], $payment_data['payment_method'], $payment_details);
        
        // customer Account Opening Email to Admin
        if ($this->walletRechargeEmailEnabled($user)) {
            try {
                EmailUtility::wallet_recharge_email('wallet_recharge_email_to_customer', $user, $payment_data['amount'], $payment_data['payment_method']);
            } catch (\Exception $e) {}
        }
        
        Session::forget('payment_data');
        Session::forget('payment_type');
        flash(translate('Recharge completed'))->success();
    }

    public function wallet_payment_email_test(){
        $user = Auth::user();
        EmailUtility::wallet_recharge_email('wallet_recharge_email_to_customer', $user, 500, 'Votku');
        echo 'OK';
    }
    
    public function offline_recharge(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_option' => ['required', 'string', 'max:100'],
            'trx_id' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        $wallet = new Wallet;
        $wallet->user_id = Auth::user()->id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = $request->payment_option;
        $wallet->payment_details = $request->trx_id;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        $wallet->reciept = $request->photo;
        $wallet->save();
        flash(translate('Offline Recharge has been done. Please wait for response.'))->success();
        return redirect()->route('wallet.index');
    }

    public function offline_recharge_request(Request $request)
    {
        $wallets = Wallet::where('offline_payment', 1);
        $type = null;
        if ($request->type != null) {
            $wallets = $wallets->where('approval', $request->type);
            $type = $request->type;
        }
        $wallets = $wallets->orderBy('id','desc')->paginate(10);
        return view('manual_payment_methods.wallet_request', compact('wallets', 'type'));
    }

    public function updateApproved(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:wallets,id'],
            'status' => ['required', 'integer', 'in:0,1,2'],
        ]);

        return DB::transaction(function () use ($request) {
            $wallet = Wallet::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (!$wallet->offline_payment) {
                throw ValidationException::withMessages([
                    'id' => translate('Only offline wallet recharge requests can be approved manually.'),
                ]);
            }

            $previousStatus = (int) $wallet->approval;
            $newStatus = (int) $request->status;
            $user = $wallet->user()->lockForUpdate()->firstOrFail();

            if ($previousStatus !== 1 && $newStatus === 1) {
                $user->balance = (float) $user->balance + (float) $wallet->amount;
                $user->save();
            } elseif ($previousStatus === 1 && $newStatus !== 1) {
                $user->balance = max(0, (float) $user->balance - (float) $wallet->amount);
                $user->save();
            }

            $wallet->approval = $newStatus;
            $wallet->save();

            return 1;
        });
    }

    private function walletRechargeEmailEnabled($user): bool
    {
        if ($user == null) {
            return false;
        }

        try {
            return (int) get_email_template_data('wallet_recharge_email_to_customer', 'status') === 1;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

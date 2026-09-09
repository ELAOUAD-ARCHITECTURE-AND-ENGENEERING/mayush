<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\WalletCollection;
use App\Models\CombinedOrder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance()
    {
        $user = User::find(auth()->user()->id);
        $latest = Wallet::where('user_id', auth()->user()->id)->latest()->first();
        return response()->json([
            'balance' => single_price($user->balance),
            'last_recharged' => $latest == null ? "Not Available" : $latest->created_at->diffForHumans(),
        ]);
    }

    public function walletRechargeHistory()
    {
        return new WalletCollection(Wallet::where('user_id', auth()->user()->id)->latest()->paginate(10));
    }

    public function processPayment(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'result' => false,
                'combined_order_id' => 0,
                'message' => translate('User not authenticated.'),
            ], 401);
        }

        $order = new OrderController;
        $request->merge([
            'payment_type' => 'wallet',
            'user_id' => $user->id,
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $order, $user) {
                $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

                $response = $order->store($request, true);
                $decoded_response = $response->getData(true);

                if (empty($decoded_response['result']) || empty($decoded_response['combined_order_id'])) {
                    return $response;
                }

                $combined_order = CombinedOrder::with('orders')->find($decoded_response['combined_order_id']);
                if (!$combined_order) {
                    return response()->json([
                        'result' => false,
                        'combined_order_id' => 0,
                        'message' => translate('Order creation failed.'),
                    ], 500);
                }

                $requiredAmount = (float) $combined_order->grand_total;
                if ($lockedUser->balance < $requiredAmount) {
                    // Insufficient funds: trigger rollback of created order
                    throw new \Exception(translate('Insufficient wallet balance'));
                }

                // Deduct balance and record ledger entry
                $lockedUser->balance -= $requiredAmount;
                $lockedUser->save();

                Wallet::create([
                    'user_id' => $lockedUser->id,
                    'amount' => -$requiredAmount,
                    'payment_method' => 'wallet',
                    'payment_details' => json_encode(['combined_order_id' => $combined_order->id]),
                    'payment_reference' => 'CO-' . $combined_order->id . '-' . time(),
                    'approval' => true,
                    'offline_payment' => false,
                ]);

                foreach ($combined_order->orders as $subOrder) {
                    calculateCommissionAffilationClubPoint($subOrder);
                }

                return $response;
            });
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'combined_order_id' => 0,
                'message' => $e->getMessage() ?: translate('Insufficient wallet balance'),
            ], 422);
        }
    }

    public function offline_recharge(Request $request)
    {
        $wallet = new Wallet;
        $wallet->user_id = auth()->user()->id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = $request->payment_option;
        $wallet->payment_details = $request->trx_id;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        $wallet->reciept = $request->photo;
        $wallet->save();
        return response()->json([
            'result' => true,
            'message' => translate('Offline Recharge has been done. Please wait for response.')
        ]);
    }

}

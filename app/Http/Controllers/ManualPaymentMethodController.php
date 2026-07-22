<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ManualPaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManualPaymentMethodController extends Controller
{
    public function index()
    {
        return 'Stub';
    }
    public function store(Request $request)
    {
        return 'Stub';
    }
    public function update(Request $request)
    {
        return 'Stub';
    }
    public function destroy($id)
    {
        return 'Stub';
    }

    public function offline_order_re_payment_modal(Request $request)
    {
        $order = Order::where('id', $request->input('order_id'))
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless(
            $order->payment_status === 'unpaid'
                && $order->delivery_status === 'pending'
                && (int) $order->manual_payment === 0,
            422,
            translate('This order is not available for payment.')
        );

        $methods = ManualPaymentMethod::query()
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 1);
            })
            ->orderBy('id')
            ->get();

        return view('frontend.user.payment_modal', compact('order', 'methods'));
    }

    public function submit_offline_payment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'payment_option' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'trx_id' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless(
            $order->payment_status === 'unpaid'
                && $order->delivery_status === 'pending'
                && (int) $order->manual_payment === 0,
            422,
            translate('This order is not available for payment.')
        );

        $methodId = null;
        if (preg_match('/^manual_payment_(\d+)$/', $validated['payment_option'], $matches)) {
            $methodId = (int) $matches[1];
        }

        $method = $methodId
            ? ManualPaymentMethod::whereKey($methodId)->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 1);
            })->first()
            : ManualPaymentMethod::where('heading', $validated['payment_option'])->first();

        abort_unless($method, 422, translate('The selected payment method is unavailable.'));

        $order->payment_type = 'manual_payment_' . $method->id;
        $order->manual_payment = 1;
        $order->manual_payment_data = json_encode([
            'name' => $validated['name'],
            'amount' => $order->grand_total,
            'trx_id' => $validated['trx_id'],
            'photo' => $validated['photo'] ?? null,
            'method_id' => $method->id,
            'method_name' => $method->heading,
        ]);
        $order->save();

        flash(translate('Payment proof submitted. It will be reviewed shortly.'))->success();

        return redirect()->route('purchase_history.details', encrypt($order->id));
    }
}

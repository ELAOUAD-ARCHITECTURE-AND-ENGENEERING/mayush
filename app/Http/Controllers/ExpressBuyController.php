<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\CombinedOrder;
use App\Models\Address;
use App\Services\PaymentVaultService;
use App\Models\ProductStock;
use App\Models\InventoryLog;
use App\Utility\EmailUtility;
use App\Utility\NotificationUtility;
use Auth;
use DB;
use Illuminate\Support\Facades\Log;

class ExpressBuyController extends Controller
{
    /**
     * Check if the user is eligible for Express Buy
     */
    public function eligibility()
    {
        if (!PaymentVaultService::isEligible()) {
            return response()->json([
                'eligible' => false,
                'reason' => 'no_default_address_or_vault_token'
            ]);
        }

        $address = Address::where('user_id', Auth::id())->where('set_default', 1)->first();

        $fingerprint = hash('sha256', request()->ip() . request()->userAgent() . session()->getId());
        session()->put('vault_session_fingerprint', $fingerprint);

        return response()->json([
            'eligible' => true,
            'preferred_payment' => PaymentVaultService::getPreferredPaymentMethod(),
            'default_address' => [
                'name' => Auth::user()->name,
                'address' => $address->address . ($address->area_id && $address->area ? ', ' . $address->area->name : ''),
                'phone' => $address->phone
            ],
            'v_token' => $fingerprint // Pass to frontend to be sent back on submit
        ]);
    }

    /**
     * Submit an express buy order
     */
    public function submit(Request $request, $product_id)
    {
        if (!PaymentVaultService::isEligible()) {
            flash(translate('Please set a default address and payment method to use Express Buy.'))->warning();
            return redirect()->route('cart');
        }

        // Security Layer: session binding check
        $expected = session()->get('vault_session_fingerprint');
        $provided = $request->input('v_token');
        if (!$expected || $provided !== $expected) {
            Log::warning('Express Buy: Session binding failed.', ['user_id' => Auth::id(), 'ip' => $request->ip()]);
            flash(translate('Security session expired. Please refresh the page and try again.'))->error();
            return back();
        }

        $quantity = $request->input('quantity', 1);
        $product = Product::findOrFail($product_id);
        $address = Address::where('user_id', Auth::id())->where('set_default', 1)->first();

        // Build address JSON for the order
        $shippingAddress = [
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'address' => $address->address . ($address->area_id && $address->area ? ', ' . $address->area->name : ''),
            'country' => ($address->country_id && $address->country) ? $address->country->name : '',
            'state' => (get_setting('has_state') == 1 && $address->state_id && $address->state) ? $address->state->name : '',
            'city' => ($address->city_id && $address->city) ? $address->city->name : '',
            'postal_code' => $address->postal_code,
            'phone' => $address->phone,
            'lat_lang' => ($address->latitude && $address->longitude) ? $address->latitude . ',' . $address->longitude : null
        ];

        DB::beginTransaction();
        try {
            // 1. Lock and deduct stock
            $product_stock = null;
            if ($product->digital != 1) {
                // Grab the first variant or default stock
                $product_stock_query = $product->stocks();
                if ($request->has('variant')) {
                    $product_stock_query->where('variant', $request->variant);
                }
                $product_stock = $product_stock_query->lockForUpdate()->first();

                if (!$product_stock) {
                    $product_stock = $product->stocks()->lockForUpdate()->first();
                }

                if (!$product_stock || $product_stock->qty < $quantity) {
                    DB::rollBack();
                    flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                    return back();
                }

                $previous_qty = $product_stock->qty;
                $product_stock->qty -= $quantity;
                $product_stock->save();

                // Log inventory change
                InventoryLog::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'quantity_delta' => -$quantity,
                    'previous_stock' => $previous_qty,
                    'current_stock' => $product_stock->qty,
                    'reason' => 'express_buy_order',
                    // Order ID assigned after order creation
                ]);
            }

            // 2. Create Combined Order
            $combined_order = new CombinedOrder;
            $combined_order->user_id = Auth::id();
            $combined_order->shipping_address = json_encode($shippingAddress);
            $combined_order->save();

            // Calculate price based on the same logic in CartController / OrderController
            // For simplicity in Phase A, we assume base price without complex cart variations unless specified
            $price = $product->unit_price; 
            if ($product_stock && $product_stock->price) {
                 $price = $product_stock->price;
            }
            // Assume 1-click Express Buy doesn't recalculate complex tier pricing unless necessary,
            // but we can deduct standard discount:
            $discount = 0;
            if ($product->discount > 0) {
                $discount = ($product->discount_type == 'percent') ? ($price * $product->discount) / 100 : $product->discount;
            }
            $finalPrice = max(0, $price - $discount);
            
            $tax = 0;
            if ($product->tax > 0) {
                 $tax = ($product->tax_type == 'percent') ? ($finalPrice * $product->tax) / 100 : $product->tax;
            }

            // Assuming free shipping or default standard flat rate for express buy
            $shipping_cost = ($product->shipping_type == 'flat_rate') ? $product->flat_shipping_cost : 0; 
            if ($product->is_quantity_multiplied && $product->shipping_type == 'flat_rate') {
                $shipping_cost *= $quantity;
            }

            // 3. Create Order
            $order = new Order;
            $order->combined_order_id = $combined_order->id;
            $order->user_id = Auth::id();
            $order->seller_id = $product->user_id;
            $order->shipping_address = $combined_order->shipping_address;
            $order->billing_address = $combined_order->shipping_address; // Same
            $order->payment_type = PaymentVaultService::getPreferredPaymentMethod();
            $order->payment_status = ($order->payment_type == 'wallet') ? 'paid' : 'unpaid';
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            $order->shipping_type = 'home_delivery'; // Default for express
            $order->grand_total = ($finalPrice + $tax) * $quantity + $shipping_cost;
            
            // Check Wallet Balance if paying with Wallet
            if ($order->payment_type == 'wallet') {
                $user = Auth::user();
                if ($user->balance < $order->grand_total) {
                    DB::rollBack();
                    flash(translate('Insufficient wallet balance to use Express Buy.'))->warning();
                    return back();
                }
                $user->balance -= $order->grand_total;
                $user->save();
            }

            $order->save();

            // 4. Create Order Detail
            $order_detail = new OrderDetail;
            $order_detail->order_id = $order->id;
            $order_detail->seller_id = $product->user_id;
            $order_detail->product_id = $product->id;
            $order_detail->product_name = $product->getTranslation('name');
            $order_detail->variation = $product_stock ? $product_stock->variant : null;
            $order_detail->price = $finalPrice * $quantity;
            $order_detail->tax = $tax * $quantity;
            $order_detail->shipping_type = 'home_delivery';
            $order_detail->shipping_cost = $shipping_cost;
            $order_detail->quantity = $quantity;
            $order_detail->payment_status = $order->payment_status;
            $order_detail->delivery_status = 'pending';
            
            if (addon_is_activated('gst_system')) {
                $order_detail->gst_rate = $product->gst_rate;
                $order_detail->gst_amount = (($order_detail->shipping_cost + $order_detail->tax + $order_detail->price)*$product->gst_rate)/100;
            }

            $order_detail->save();

            // Update inventory log
            if ($product->digital != 1) {
                InventoryLog::where('product_id', $product->id)
                    ->where('user_id', Auth::id())
                    ->where('reason', 'express_buy_order')
                    ->whereNull('order_id')
                    ->update(['order_id' => $order->id]);
            }

            // Stats
            $product->num_of_sale += $quantity;
            $product->save();

            if ($product->added_by == 'seller' && $product->user->seller) {
                $product->user->seller->num_of_sale += $quantity;
                $product->user->seller->save();
            }

            // Update Combined Order
            $combined_order->grand_total = $order->grand_total;
            $combined_order->save();

            DB::commit();

            // 5. Send Notifications
            try {
                EmailUtility::order_email($order, $order->delivery_status);
                NotificationUtility::sendNotification($order, $order->delivery_status);
            } catch (\Exception $mailErr) {
                Log::warning('Express Buy: Notification failed.', ['error' => $mailErr->getMessage()]);
            }

            $request->session()->put('combined_order_id', $combined_order->id);

            // CMI Vault execution for express charge
            $token = PaymentVaultService::getActiveToken(Auth::id());
            if ($order->payment_type == 'cmi_vault' && $token) {
                return app(\App\Http\Controllers\Payment\CmiController::class)->expressCharge($combined_order->id, $token);
            }

            // Redirect for cash on delivery or wallet
            return redirect()->route('order_confirmed');

        } catch (\Exception $e) {
            DB::rollBack();
            flash(translate('An error occurred during Express Buy: ' . $e->getMessage()))->error();
            return back();
        }
    }
}

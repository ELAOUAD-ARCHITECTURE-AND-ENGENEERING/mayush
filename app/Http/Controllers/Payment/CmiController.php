<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\CombinedOrder;
use App\Models\User;
use App\Models\EliteSubscription;
use App\Models\PaymentAttempt;
use App\Models\CmiCallbackLog;
use App\Services\PaymentStateService;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\Seller\SellerEliteController;
use App\Http\Requests\CmiCallbackRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use App\Services\Payment\CmiConfigValidatorInterface;

class CmiController extends Controller
{
    protected CmiConfigValidatorInterface $configValidator;
    protected PaymentStateService $paymentStateService;

    public function __construct(CmiConfigValidatorInterface $configValidator, PaymentStateService $paymentStateService)
    {
        $this->configValidator = $configValidator;
        $this->paymentStateService = $paymentStateService;
    }

    public function pay(Request $request)
    {
        Log::info('CMI Payment Initiated', ['user_id' => Auth::id(), 'payment_type' => Session::get('payment_type')]);

        if (!Session::has('combined_order_id')) {
            $paymentType = Session::get('payment_type');
            $nonCartPayments = ['order_re_payment', 'wallet_payment', 'customer_package_payment', 'seller_package_payment', 'elite_payment'];
            
            if (!$paymentType || !in_array($paymentType, $nonCartPayments)) {
                 Log::error('CMI Payment Error: Session expired or combined_order_id missing.', ['payment_type' => $paymentType]);
                 Session::flash('error', translate('Session expired or invalid order. Please try again.'));
                 return redirect()->route('home');
            }
        }

        try {
            $data = [];
            $user = Auth::user();
            
            if (!$user) {
                Log::error('CMI Payment Error: User not authenticated.');
                Session::flash('error', translate('Please login to continue with payment.'));
                return redirect()->route('user.login');
            }
            
            if (Session::has('payment_type')) {
                $paymentType = Session::get('payment_type');
                $paymentData = Session::get('payment_data');
                $amount = 0;
                $oid = '';
                $shipping_info = [];
    
                if ($paymentType == 'cart_payment') {
                    $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                    $amount = round($combined_order->grand_total, 2);
                    $oid = 'CO-' . $combined_order->id . '-' . time();
                    $shipping_info = json_decode($combined_order->shipping_address, true) ?? [];
                } elseif ($paymentType == 'order_re_payment') {
                    $order = Order::findOrFail($paymentData['order_id']);
                    $amount = round($order->grand_total, 2);
                    $oid = 'OR-' . $order->id . '-' . time();
                    $shipping_info = json_decode($order->shipping_address, true) ?? [];
                } elseif ($paymentType == 'wallet_payment') {
                    $amount = round($paymentData['amount'], 2);
                    $oid = 'W-' . Auth::id() . '-' . time();
                    \Cache::put('cmi_wallet_amount_' . $oid, $amount, 3600);
                    $shipping_info = ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone];
                } elseif ($paymentType == 'customer_package_payment') {
                    $customer_package = \App\Models\CustomerPackage::findOrFail($paymentData['customer_package_id']);
                    $amount = round($customer_package->amount, 2);
                    $oid = 'CP-' . $customer_package->id . '-' . time();
                    $shipping_info = ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone];
                } elseif ($paymentType == 'seller_package_payment') {
                    $seller_package = \App\Models\SellerPackage::findOrFail($paymentData['seller_package_id']);
                    $amount = round($seller_package->amount, 2);
                    $oid = 'SP-' . $seller_package->id . '-' . time();
                    $shipping_info = ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone];
                } elseif ($paymentType == 'elite_payment') {
                    $eliteSub = EliteSubscription::findOrFail($paymentData['subscription_id']);
                    $amount = round($eliteSub->amount_paid, 2);
                    $oid = 'EA-' . $eliteSub->id . '-' . time();
                    $shipping_info = ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone];
                }
    
                if (!is_array($shipping_info)) $shipping_info = [];
    
                $validationResult = $this->configValidator->validate();
                if (!$validationResult->isValid) {
                    Log::critical('CMI Configuration Validation Failed', ['errors' => $validationResult->errors]);
                    Session::flash('error', translate('Payment gateway is not available. Please contact support.'));
                    return redirect()->route('home');
                }
    
                $clientId = config('cmi.merchant_id');
                $storeKey = config('cmi.secret_key');
                $storeType = config('cmi.store_type');

                $data['clientid'] = $clientId;
                $data['amount'] = $amount;
                $data['okUrl'] = config('cmi.ok_url') ?: route('cmi.success');
                $data['failUrl'] = config('cmi.fail_url') ?: route('cmi.fail');
                $data['callbackUrl'] = config('cmi.callback_url') ?: route('cmi.callback');
                $data['TranType'] = "PreAuth";
                $data['shopurl'] = route('home');
                $data['currency'] = "504";
                $data['rnd'] = microtime();
                $data['storetype'] = $storeType;
                $data['hashAlgorithm'] = "ver3";
                $data['lang'] = Session::get('locale', 'fr');
                $data['refreshtime'] = "5";
                
                $data['BillToName'] = $this->str_without_accents($shipping_info['name'] ?? $user->name ?? 'Customer');
                $data['BillToCompany'] = $this->str_without_accents($shipping_info['company'] ?? '');
                $data['BillToStreet1'] = $this->str_without_accents($shipping_info['address'] ?? '');
                $data['BillToCity'] = $this->str_without_accents($shipping_info['city'] ?? '');
                $data['BillToStateProv'] = $this->str_without_accents($shipping_info['state'] ?? '');
                $data['BillToPostalCode'] = $this->str_without_accents($shipping_info['postal_code'] ?? '');
                $data['BillToCountry'] = $this->str_without_accents($shipping_info['country'] ?? '504');
                $data['email'] = $shipping_info['email'] ?? $user->email ?? 'email@domain.com';
                $data['tel'] = $this->str_without_accents($shipping_info['phone'] ?? $user->phone ?? '0000000000');
                $data['oid'] = $oid;
                $data['encoding'] = "UTF-8";
    
                if (isset($paymentData['save_card']) && $paymentData['save_card']) {
                    \Cache::put('cmi_save_card_' . $oid, true, 3600);
                }

                $data['hash'] = $this->generateHash($data, $storeKey);
    
                $actionUrl = $this->configValidator->getGatewayUrl();

                PaymentAttempt::create([
                    'user_id' => $user->id,
                    'combined_order_id' => $paymentType == 'cart_payment' ? Session::get('combined_order_id') : null,
                    'order_id' => $paymentType == 'order_re_payment' ? $paymentData['order_id'] : null,
                    'payment_method' => $paymentType,
                    'gateway' => 'cmi',
                    'merchant_reference' => $oid,
                    'amount' => $amount,
                    'currency' => 'MAD',
                    'status' => 'initiated',
                    'initiated_at' => now(),
                    'request_payload_hash' => hash('sha256', json_encode($data))
                ]);

                return view('frontend.payment.cmi', compact('data', 'actionUrl'));
            }
        } catch (\Exception $e) {
            Log::error('CMI Payment Critical Error: ' . $e->getMessage());
            Session::flash('error', translate('Something went wrong with the payment gateway.'));
            return redirect()->route('home');
        }
        
        Session::flash('error', translate('Invalid payment session.'));
        return redirect()->route('home');
    }

    public function callback(CmiCallbackRequest $request)
    {
        $input = $request->all();
        $payloadHash = hash('sha256', json_encode($input));

        $callbackLog = CmiCallbackLog::create([
            'gateway' => 'cmi',
            'merchant_reference' => $input['oid'] ?? null,
            'gateway_reference' => $input['TransId'] ?? null,
            'payload_hash' => $payloadHash,
            'raw_payload' => $input,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'received_at' => now(),
            'processing_status' => 'received'
        ]);

        try {
            $storeKey = config('cmi.secret_key');
            $actualHash = $this->generateHash($input, $storeKey);
            $retrievedHash = $input["HASH"] ?? '';

            if ($retrievedHash !== $actualHash) {
                $callbackLog->update([
                    'processing_status' => 'rejected',
                    'error_message' => 'Hash Mismatch',
                    'processed_at' => now()
                ]);
                return response('FAILURE')->header('Content-Type', 'text/plain'); 
            }

            $callbackLog->update(['signature_valid' => true]);

            if (isset($input['oid'])) {
                $parts = explode('-', $input['oid']);
                if (count($parts) >= 2) {
                    $type = $parts[0];
                    $id = $parts[1];
                    $amount_received = (float)($input['amount'] ?? 0);
                    $order = null;
                    $db_amount = 0;

                    // Idempotency DB check
                    $existingProcessedLog = CmiCallbackLog::where('gateway_reference', $input['TransId'] ?? $input['oid'])
                        ->where('processing_status', 'processed')
                        ->where('signature_valid', true)
                        ->first();
                        
                    if ($existingProcessedLog) {
                        $callbackLog->update([
                            'is_duplicate' => true,
                            'processing_status' => 'duplicate',
                            'processed_at' => now()
                        ]);
                        return response('ACTION=POSTAUTH')->header('Content-Type', 'text/plain');
                    }

                    if ($type == 'CO') {
                        $order = CombinedOrder::find($id);
                        $db_amount = $order ? (float)$order->grand_total : 0;
                    } elseif ($type == 'OR') {
                        $order = Order::find($id);
                        $db_amount = $order ? (float)$order->grand_total : 0;
                    } elseif ($type == 'W') {
                         $order = User::find($id);
                         $cached_amount = \Cache::get('cmi_wallet_amount_' . $input['oid']);
                         if ($cached_amount) {
                             $db_amount = (float) $cached_amount;
                         } else {
                             $callbackLog->update(['processing_status' => 'rejected', 'error_message' => 'Wallet cache missing', 'processed_at' => now()]);
                             return response('FAILURE')->header('Content-Type', 'text/plain');
                         }
                    } elseif ($type == 'EA') {
                         $order = EliteSubscription::find($id);
                         $db_amount = $order ? (float) $order->amount_paid : 0;
                    }

                    if (!$order) {
                        $callbackLog->update(['processing_status' => 'rejected', 'error_message' => 'Order not found', 'processed_at' => now()]);
                        return response('FAILURE')->header('Content-Type', 'text/plain');
                    }

                    if (abs($db_amount - $amount_received) > 0.01) {
                        $callbackLog->update(['processing_status' => 'rejected', 'error_message' => 'Amount Mismatch', 'processed_at' => now()]);
                        return response('FAILURE')->header('Content-Type', 'text/plain');
                    }

                    if (isset($input["ProcReturnCode"]) && $input["ProcReturnCode"] == "00") {
                        
                        $paymentDetails = $input;
                        
                        // Safe State Transition using row locks
                        $wasChanged = $this->paymentStateService->markOrderPaidSafely($order, $paymentDetails);
                        
                        if (!$wasChanged) {
                            $callbackLog->update([
                                'is_duplicate' => true,
                                'processing_status' => 'duplicate',
                                'error_message' => 'Order already paid',
                                'processed_at' => now()
                            ]);
                            return response('ACTION=POSTAUTH')->header('Content-Type', 'text/plain');
                        }

                        // Capture CMI Vault Token
                        if ($type == 'CO' && isset($input['TransId'])) {
                            $userId = $order->user_id ?? ($order->orders->first()->user_id ?? null);
                            $optIn = \Cache::get('cmi_save_card_' . $input['oid']);
                            if ($userId && $optIn) {
                                \App\Services\PaymentVaultService::storeToken($userId, $input);
                                \Cache::forget('cmi_save_card_' . $input['oid']);
                            }
                        } elseif ($type == 'W' && $order instanceof User) {
                            $user = $order;
                            app(\App\Services\WalletService::class)->credit($user, $amount_received, 'cmi', json_encode($paymentDetails));
                        } elseif ($type == 'EA' && $order instanceof EliteSubscription) {
                            $txnId = $input['TransId'] ?? $input['oid'] ?? null;
                            SellerEliteController::activateSubscription($order->id, json_encode($paymentDetails), $txnId);
                        }

                        PaymentAttempt::where('merchant_reference', $input['oid'])
                            ->update(['status' => 'paid', 'completed_at' => now(), 'response_payload_hash' => $payloadHash]);

                        $callbackLog->update([
                            'processing_status' => 'processed',
                            'processed_at' => now()
                        ]);

                        if ($type == 'W') \Cache::forget('cmi_wallet_amount_' . $input['oid']);

                        return response('ACTION=POSTAUTH')->header('Content-Type', 'text/plain');
                    } else {
                        PaymentAttempt::where('merchant_reference', $input['oid'])
                            ->update(['status' => 'failed', 'failed_at' => now(), 'response_payload_hash' => $payloadHash]);
                            
                        $callbackLog->update([
                            'processing_status' => 'failed',
                            'error_message' => 'ProcReturnCode != 00',
                            'processed_at' => now()
                        ]);
                        return response('APPROVED')->header('Content-Type', 'text/plain');
                    }
                }
            }

            $callbackLog->update(['processing_status' => 'rejected', 'error_message' => 'Invalid OID Format', 'processed_at' => now()]);
            return response('FAILURE')->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            $callbackLog->update(['processing_status' => 'failed', 'error_message' => $e->getMessage(), 'processed_at' => now()]);
            return response('FAILURE')->header('Content-Type', 'text/plain');
        }
    }

    public function success(Request $request)
    {
        $storeKey = config('cmi.secret_key');
        $input = $request->all();

        if ($request->isMethod('get') && !isset($input['HASH'])) {
            if (Session::has('combined_order_id')) return redirect()->route('order_confirmed');
            return redirect()->route('home');
        }
        
        $actualHash = $this->generateHash($input, $storeKey);
        $retrievedHash = $input["HASH"] ?? '';

        if ($retrievedHash != $actualHash) {
             Session::put('payment_error', translate("Payment security verification failed."));
             return redirect()->route('payment.failed');
        }
        
        if (isset($input["ProcReturnCode"]) && $input["ProcReturnCode"] == "00") {
             Session::flash('success', translate("Payment successful"));
             if (Session::has('payment_type')) {
                $paymentType = Session::get('payment_type');
                $paymentData = Session::get('payment_data');
                $paymentDetails = json_encode($input);
                
                if ($paymentType == 'cart_payment') {
                    $combined_order_id = Session::get('combined_order_id');
                    if (!$combined_order_id && isset($input['oid'])) {
                        $parts = explode('-', $input['oid']);
                        if (count($parts) >= 2 && $parts[0] == 'CO') {
                            $combined_order_id = $parts[1];
                            Session::put('combined_order_id', $combined_order_id);
                        }
                    }
                    if ($combined_order_id) {
                        return (new CheckoutController)->checkout_done($combined_order_id, $paymentDetails);
                    }
                } elseif ($paymentType == 'order_re_payment') {
                    return (new CheckoutController)->orderRePaymentDone($paymentData, $paymentDetails);
                } elseif ($paymentType == 'wallet_payment') {
                    return (new WalletController)->wallet_payment_done($paymentData, $paymentDetails);
                } elseif ($paymentType == 'customer_package_payment') {
                    return (new CustomerPackageController)->purchase_payment_done($paymentData, $paymentDetails);
                } elseif ($paymentType == 'seller_package_payment') {
                    return (new SellerPackageController)->purchase_payment_done($paymentData, $paymentDetails);
                } elseif ($paymentType == 'elite_payment') {
                    return redirect()->route('seller.elite.payment.success');
                }
             }
             if (isset($input['oid'])) {
                 $parts = explode('-', $input['oid']);
                 if (count($parts) >= 2 && $parts[0] == 'CO') {
                     return redirect()->route('order_confirmed_with_id', ['combined_order_id' => $parts[1]]);
                 }
             }
             return redirect()->route('order_confirmed');
        } else {
            if (isset($input['oid'])) {
                $parts = explode('-', $input['oid']);
                if (count($parts) >= 2) {
                    if ($parts[0] == 'CO') {
                        Session::put('combined_order_id', $parts[1]);
                        Session::put('payment_type', 'cart_payment');
                    } elseif ($parts[0] == 'OR') {
                        Session::put('payment_type', 'order_re_payment');
                        Session::put('payment_data', ['order_id' => $parts[1]]);
                    }
                }
            }
            Session::put('payment_error', $input['ErrMsg'] ?? translate("Your payment was not successful."));
            return redirect()->route('payment.failed');
        }
    }

    public function fail(Request $request)
    {
        $input = $request->all();
        $storeKey = config('cmi.secret_key');
        if (isset($input['HASH'])) {
            $actualHash = $this->generateHash($input, $storeKey);
            if ($input['HASH'] === $actualHash && isset($input['oid'])) {
                $parts = explode('-', $input['oid']);
                if (count($parts) >= 2) {
                    if ($parts[0] == 'CO') {
                        Session::put('combined_order_id', $parts[1]);
                        Session::put('payment_type', 'cart_payment');
                    } elseif ($parts[0] == 'OR') {
                        Session::put('payment_type', 'order_re_payment');
                        Session::put('payment_data', ['order_id' => $parts[1]]);
                    }
                }
            }
        }
        Session::put('payment_error', $input['ErrMsg'] ?? translate("Payment was cancelled or failed."));
        return redirect()->route('payment.failed');
    }

    private function generateHash($data, $storeKey)
    {
        $postParams = array_keys($data);
        natcasesort($postParams);

        $hashval = "";
        foreach ($postParams as $param){
            $paramValue = $data[$param];
            $paramValue = html_entity_decode(preg_replace("/\n$/","", $paramValue), ENT_QUOTES, 'UTF-8');
            $paramValue = preg_replace('/document./i', 'document.', $paramValue);
            $escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue)); 
            
            $lowerParam = strtolower($param);
            if($lowerParam != "hash" && $lowerParam != "encoding" ) {
                $hashval = $hashval . $escapedParamValue . "|";
            }
        }

        $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
        $hashval = $hashval . $escapedStoreKey;
        $calculatedHashValue = hash('sha512', $hashval);
        return base64_encode(pack('H*', $calculatedHashValue));
    }

    private function str_without_accents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str);
        $str = preg_replace('/[^a-zA-Z0-9_ -]/s','',$str);
        return trim($str);
    }

    public function expressCharge($combinedOrderId, $tokenObj)
    {
        try {
            $combined_order = CombinedOrder::findOrFail($combinedOrderId);
            $amount = round($combined_order->grand_total, 2);
            $oid = 'CO-' . $combined_order->id . '-' . time();
            $user = Auth::user();

            $clientId = config('cmi.merchant_id');
            $storeKey = config('cmi.secret_key');
            $storeType = config('cmi.store_type');
            
            $data = [];
            $data['clientid'] = $clientId;
            $data['amount'] = $amount;
            $data['oid'] = $oid;
            $data['TranType'] = "PreAuth";
            $data['isRecurring'] = "true";
            $data['recurringTrxnRef'] = $tokenObj->token;
            $data['currency'] = "504";
            $data['rnd'] = microtime();
            $data['storetype'] = $storeType;
            $data['hashAlgorithm'] = "ver3";
            $data['lang'] = Session::get('locale', 'fr');
            $data['encoding'] = "UTF-8";
            $address = Address::where('user_id', $user->id)->where('set_default', 1)->first();
            
            $data['email'] = $user->email ?? 'email@domain.com';
            $data['tel'] = $this->str_without_accents($user->phone ?? '0000000000');
            $data['BillToName'] = $this->str_without_accents($user->name ?: 'Customer');
            if ($address) {
                $data['BillToStreet1'] = $this->str_without_accents($address->address);
                $data['BillToCity'] = $this->str_without_accents($address->city ? $address->city->name : '');
                $data['BillToStateProv'] = $this->str_without_accents($address->state ? $address->state->name : '');
                $data['BillToPostalCode'] = $this->str_without_accents($address->postal_code);
                $data['BillToCountry'] = $this->str_without_accents($address->country ? $address->country->name : '504');
            }
            
            $data['okUrl'] = config('cmi.ok_url') ?: route('cmi.success');
            $data['failUrl'] = config('cmi.fail_url') ?: route('cmi.fail');
            $data['callbackUrl'] = config('cmi.callback_url') ?: route('cmi.callback');

            $data['hash'] = $this->generateHash($data, $storeKey);
            $actionUrl = config('cmi.gateway_url');

            PaymentAttempt::create([
                'user_id' => $user->id,
                'combined_order_id' => $combinedOrderId,
                'payment_method' => 'express_buy_cmi_vault',
                'gateway' => 'cmi',
                'merchant_reference' => $oid,
                'amount' => $amount,
                'currency' => 'MAD',
                'status' => 'initiated',
                'initiated_at' => now(),
                'request_payload_hash' => hash('sha256', json_encode($data))
            ]);

            return view('frontend.payment.cmi', compact('data', 'actionUrl'));

        } catch (\Exception $e) {
            $combined_order = CombinedOrder::find($combinedOrderId);
            if ($combined_order) {
                foreach ($combined_order->orders as $subOrder) {
                    $subOrder->delete();
                }
                $combined_order->delete();
            }
            Session::flash('error', translate('Failed to process Express Buy setup.'));
            return redirect()->route('home');
        }
    }
}

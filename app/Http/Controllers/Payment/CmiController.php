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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\Seller\SellerEliteController;
use App\Http\Requests\CmiCallbackRequest;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class CmiController extends Controller
{
    public function pay(Request $request)
    {
        // Debugging: Log entry
        Log::info('CMI Payment Initiated', ['user_id' => Auth::id(), 'session_data' => Session::all()]);

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
            
            // Check if user is authenticated
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
                    $oid = 'CO-' . $combined_order->id . '-' . time(); // Unique Order ID
                    $shipping_info = json_decode($combined_order->shipping_address, true) ?? [];
                } elseif ($paymentType == 'order_re_payment') {
                    $order = Order::findOrFail($paymentData['order_id']);
                    $amount = round($order->grand_total, 2);
                    $oid = 'OR-' . $order->id . '-' . time();
                    $shipping_info = json_decode($order->shipping_address, true) ?? [];
                } elseif ($paymentType == 'wallet_payment') {
                    $amount = round($paymentData['amount'], 2);
                    $oid = 'W-' . Auth::id() . '-' . time();
                    $shipping_info = [
                        'name' => $user->name ?? 'Guest',
                        'email' => $user->email ?? 'email@domain.com',
                        'phone' => $user->phone ?? '0000000000'
                    ];
                } elseif ($paymentType == 'customer_package_payment') {
                    $customer_package = \App\Models\CustomerPackage::findOrFail($paymentData['customer_package_id']);
                    $amount = round($customer_package->amount, 2);
                    $oid = 'CP-' . $customer_package->id . '-' . time();
                     $shipping_info = [
                        'name' => $user->name ?? 'Guest',
                        'email' => $user->email ?? 'email@domain.com',
                        'phone' => $user->phone ?? '0000000000'
                    ];
                } elseif ($paymentType == 'seller_package_payment') {
                    $seller_package = \App\Models\SellerPackage::findOrFail($paymentData['seller_package_id']);
                    $amount = round($seller_package->amount, 2);
                    $oid = 'SP-' . $seller_package->id . '-' . time();
                     $shipping_info = [
                        'name' => $user->name ?? 'Guest',
                        'email' => $user->email ?? 'email@domain.com',
                        'phone' => $user->phone ?? '0000000000'
                    ];
                } elseif ($paymentType == 'elite_payment') {
                    $eliteSub = EliteSubscription::findOrFail($paymentData['subscription_id']);
                    $amount = round($eliteSub->amount_paid, 2);
                    $oid = 'EA-' . $eliteSub->id . '-' . time();
                    $shipping_info = [
                        'name' => $user->name ?? 'Guest',
                        'email' => $user->email ?? 'email@domain.com',
                        'phone' => $user->phone ?? '0000000000'
                    ];
                }
    
                // Ensure shipping_info is always an array
                if (!is_array($shipping_info)) {
                    $shipping_info = [];
                }
    
                // CMI Credentials & Config from Config File (Strictly from .env / config for security)
                $clientId = config('cmi.merchant_id');
                $storeKey = config('cmi.secret_key');
                $storeType = config('cmi.store_type');
    
                // Validate Credentials
                if (empty($clientId) || empty($storeKey)) {
                     Log::critical('CMI Setup Error: Missing Merchant ID or Secret Key.');
                     Session::flash('error', translate('Payment gateway configuration error. Please contact support.'));
                     return redirect()->route('home');
                }

                $data['clientid'] = $clientId;
                $data['amount'] = $amount;
                
                // URL Configuration
                $data['okUrl'] = config('cmi.ok_url') ?: route('cmi.success');
                $data['failUrl'] = config('cmi.fail_url') ?: route('cmi.fail');
                $data['callbackUrl'] = config('cmi.callback_url') ?: route('cmi.callback');
                
                $data['TranType'] = "PreAuth";
                $data['shopurl'] = route('home');
                $data['currency'] = "504"; // MAD ISO Code
                $data['rnd'] = microtime();
                $data['storetype'] = $storeType;
                $data['hashAlgorithm'] = "ver3";
                $data['lang'] = Session::get('locale', 'fr');
                $data['refreshtime'] = "5";
                
                // Safely extract shipping info with fallbacks
                $billToName = $shipping_info['name'] ?? $user->name ?? 'Guest';
                $billToCompany = $shipping_info['company'] ?? $user->name ?? '';
                $billToStreet = $shipping_info['address'] ?? '';
                $billToCity = $shipping_info['city'] ?? '';
                $billToState = $shipping_info['state'] ?? '';
                $billToPostalCode = $shipping_info['postal_code'] ?? '';
                $billToCountry = $shipping_info['country'] ?? '504';
                $email = $shipping_info['email'] ?? $user->email ?? 'email@domain.com';
                $phone = $shipping_info['phone'] ?? $user->phone ?? '0000000000';
                
                // Populate BillTo Fields with accent removal and mandatory fallbacks
                $data['BillToName'] = $this->str_without_accents($billToName) ?: 'Customer';
                $data['BillToCompany'] = $this->str_without_accents($billToCompany);
                $data['BillToStreet1'] = $this->str_without_accents($billToStreet);
                $data['BillToCity'] = $this->str_without_accents($billToCity);
                $data['BillToStateProv'] = $this->str_without_accents($billToState);
                $data['BillToPostalCode'] = $this->str_without_accents($billToPostalCode);
                $data['BillToCountry'] = $this->str_without_accents($billToCountry);
                
                $data['email'] = $email;
                $data['tel'] = $this->str_without_accents($phone);
                
                $data['oid'] = $oid;
                $data['encoding'] = "UTF-8";
    
                // Security: Cache the save_card preference for the callback (OID-linked)
                if (isset($paymentData['save_card']) && $paymentData['save_card']) {
                    \Cache::put('cmi_save_card_' . $oid, true, 3600); // 1 hour TTL
                }

                // Hash Calculation
                $data['hash'] = $this->generateHash($data, $storeKey);
    
                // Gateway URL
                $actionUrl = config('cmi.gateway_url');
                
                Log::info('CMI Form Data Generated', ['oid' => $oid, 'amount' => $amount]);

                return view('frontend.payment.cmi', compact('data', 'actionUrl'));
            } else {
                Log::error('CMI Setup Error: No payment_type in session.');
            }
        } catch (\Exception $e) {
            Log::error('CMI Payment Critical Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            Session::flash('error', translate('Something went wrong with the payment gateway configuration.'));
            return redirect()->route('home');
        }
        
        Session::flash('error', translate('Invalid payment session.'));
        return redirect()->route('home');
    }

    public function callback(CmiCallbackRequest $request)
    {
        $input = $request->all();
        
        Log::info('CMI Callback: Request received', [
            'oid' => $input['oid'] ?? 'unknown',
            'amount' => $input['amount'] ?? 'missing',
            'ProcReturnCode' => $input['ProcReturnCode'] ?? 'missing',
            'method' => $request->method(),
            'clientIp' => $request->ip()
        ]);
        
        try {
            // Config key (Strictly from .env / config for security)
            $storeKey = config('cmi.secret_key');

            // 1. Verify Hash
            $actualHash = $this->generateHash($input, $storeKey);
            $retrievedHash = $input["HASH"] ?? '';

            if ($retrievedHash !== $actualHash) {
                Log::error('CMI Callback: Hash Mismatch', [
                    'oid' => $input['oid'] ?? 'unknown',
                    'calculated' => $actualHash, 
                    'received' => $retrievedHash
                ]);
                return response('FAILURE')->header('Content-Type', 'text/plain'); 
            }

            // 2. Identify and Validate Order Amount
            if (isset($input['oid'])) {
                $parts = explode('-', $input['oid']);
                if (count($parts) >= 2) {
                    $type = $parts[0];
                    $id = $parts[1];
                    $amount_received = (float)($input['amount'] ?? 0);
                    $order = null;
                    $db_amount = 0;

                    if ($type == 'CO') {
                        $order = CombinedOrder::find($id);
                        $db_amount = $order ? (float)$order->grand_total : 0;
                    } elseif ($type == 'OR') {
                        $order = Order::find($id);
                        $db_amount = $order ? (float)$order->grand_total : 0;
                    } elseif ($type == 'W') {
                         // Wallet amount check (Wallet uses the 'amount' from input as the source of truth usually, 
                         // but for safety in callback we assume it's valid if user_id $id exists)
                         $order = User::find($id);
                         $db_amount = $amount_received; 
                    } elseif ($type == 'EA') {
                         $order = EliteSubscription::find($id);
                         $db_amount = $order ? (float) $order->amount_paid : 0;
                    }

                    if (!$order) {
                        Log::error('CMI Callback: Order not found', ['oid' => $input['oid']]);
                        return response('FAILURE')->header('Content-Type', 'text/plain');
                    }

                    // 2.5 Idempotency Check
                    if ($type == 'CO' && $order instanceof CombinedOrder) {
                        $allPaid = true;
                        foreach ($order->orders as $subOrder) {
                            if ($subOrder->payment_status != 'paid') {
                                $allPaid = false;
                                break;
                            }
                        }
                        if ($allPaid && count($order->orders) > 0) {
                            Log::info('CMI Callback: Idempotency - CombinedOrder already paid', ['oid' => $input['oid']]);
                            return response('ACTION=POSTAUTH')->header('Content-Type', 'text/plain');
                        }
                    } elseif ($type == 'OR' && $order instanceof Order) {
                        if ($order->payment_status == 'paid') {
                            Log::info('CMI Callback: Idempotency - Order already paid', ['oid' => $input['oid']]);
                            return response('ACTION=POSTAUTH')->header('Content-Type', 'text/plain');
                        }
                    } elseif ($type == 'EA' && $order instanceof EliteSubscription) {
                        if (isset($order->payment_status) && $order->payment_status == 'paid') {
                            Log::info('CMI Callback: Idempotency - Elite Subscription already paid', ['oid' => $input['oid']]);
                            return response('ACTION=POSTAUTH')->header('Content-Type', 'text/plain');
                        }
                    }

                    // 3. Amount Validation (Merchant Control per V2.0 docs)
                    if (abs($db_amount - $amount_received) > 0.01) {
                        Log::error('CMI Callback: Amount Mismatch', [
                            'oid' => $input['oid'],
                            'db_amount' => $db_amount,
                            'received_amount' => $amount_received
                        ]);
                        return response('FAILURE')->header('Content-Type', 'text/plain');
                    }

                    // 4. Check ProcReturnCode
                    if (isset($input["ProcReturnCode"]) && $input["ProcReturnCode"] == "00") {
                        Log::info('CMI Callback: Success authorization (00)', ['oid' => $input['oid']]);
                        
                        $payment_details = json_encode($input);
                        
                        // Update Order Status
                        if ($type == 'CO' && $order instanceof CombinedOrder) {
                            foreach ($order->orders as $subOrder) {
                                $subOrder->payment_status = 'paid';
                                $subOrder->payment_details = $payment_details;
                                $subOrder->save();
                            }

                            // Capture CMI Vault Token ONLY if user opted-in
                            if (isset($input['TransId'])) {
                                $userId = $order->user_id ?? ($order->orders->first()->user_id ?? null);
                                $optIn = \Cache::get('cmi_save_card_' . $input['oid']);
                                
                                if ($userId && $optIn) {
                                    \App\Services\PaymentVaultService::storeToken($userId, $input);
                                    Log::info('CMI Vault: Token stored for user (Opt-in confirmed)', ['transId' => $input['TransId']]);
                                    \Cache::forget('cmi_save_card_' . $input['oid']);
                                } else {
                                    Log::info('CMI Vault: Token skipped (No opt-in)', ['oid' => $input['oid']]);
                                }
                            }

                        } elseif ($type == 'OR' && $order instanceof Order) {
                            $order->payment_status = 'paid';
                            $order->payment_details = $payment_details;
                            $order->save();
                        } elseif ($type == 'W' && $order instanceof User) {
                            $user = $order;
                            $user->balance += $amount_received;
                            $user->save();

                            $wallet = new \App\Models\Wallet;
                            $wallet->user_id = $user->id;
                            $wallet->amount = $amount_received;
                            $wallet->payment_method = 'cmi';
                            $wallet->payment_details = $payment_details;
                            $wallet->save();
                        } elseif ($type == 'EA' && $order instanceof EliteSubscription) {
                            // Activate Elite subscription via dedicated handler
                            $txnId = $input['TransId'] ?? $input['oid'] ?? null;
                            SellerEliteController::activateSubscription($order->id, $payment_details, $txnId);
                            Log::info('CMI Callback: Elite subscription activated', ['subscription_id' => $order->id]);
                        }

                        Log::info('CMI Callback: Returning ACTION=POSTAUTH');
                        return response('ACTION=POSTAUTH')->header('Content-Type', 'text/plain');
                    } else {
                        // V2.0 Documentation: If ProcReturnCode <> 00, it's a failure.
                        // Do not change status. Return "APPROVED" to acknowledge.
                        Log::warning('CMI Callback: Payment Rejected/Failed', [
                            'oid' => $input['oid'],
                            'ProcReturnCode' => $input['ProcReturnCode'] ?? 'missing'
                        ]);
                        
                        return response('APPROVED')->header('Content-Type', 'text/plain');
                    }
                }
            }

            Log::error('CMI Callback: Invalid OID format');
            return response('FAILURE')->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            Log::critical('CMI Callback: Internal Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Emergency fallback for CMI - return FAILURE string to bypass empty response
            return response('FAILURE')->header('Content-Type', 'text/plain');
        }
    }

    public function success(Request $request)
    {
        $storeKey = config('cmi.secret_key');
        
        $input = $request->all();

        if ($request->isMethod('get') && !isset($input['HASH'])) {
            if (Session::has('combined_order_id')) {
                return redirect()->route('order_confirmed');
            }
            return redirect()->route('home');
        }
        
         $actualHash = $this->generateHash($input, $storeKey);
         $retrievedHash = $input["HASH"] ?? '';

         if ($retrievedHash != $actualHash) {
             Log::error('CMI Success Page Hash Mismatch', ['calculated' => $actualHash, 'received' => $retrievedHash, 'method' => $request->method()]);
             Session::put('payment_error', translate("Payment security verification failed."));
             return redirect()->route('payment.failed');
         }
        
        if (isset($input["ProcReturnCode"]) && $input["ProcReturnCode"] == "00") {
             Session::flash('success', translate("Payment successful"));
             
             Log::info('CMI Payment Success - Processing', [
                 'ProcReturnCode' => $input["ProcReturnCode"],
                 'oid' => $input['oid'] ?? 'unknown',
                 'session_data' => Session::all(),
                 'has_payment_type' => Session::has('payment_type')
             ]);
             
             if (Session::has('payment_type')) {
                $paymentType = Session::get('payment_type');
                $paymentData = Session::get('payment_data');
                $paymentDetails = json_encode($input);
                
                if ($paymentType == 'cart_payment') {
                    $combined_order_id = Session::get('combined_order_id');
                    if (!$combined_order_id) {
                        Log::error('CMI Payment: combined_order_id not found in session');
                        // Try to extract from payment data
                        if (isset($input['oid'])) {
                            $parts = explode('-', $input['oid']);
                            if (count($parts) >= 2 && $parts[0] == 'CO') {
                                $combined_order_id = $parts[1];
                                Session::put('combined_order_id', $combined_order_id);
                                Log::info('CMI Payment: Restored combined_order_id from oid', ['combined_order_id' => $combined_order_id]);
                            }
                        }
                    }
                    
                    if ($combined_order_id) {
                        return (new CheckoutController)->checkout_done($combined_order_id, $paymentDetails);
                    } else {
                        Log::error('CMI Payment: Unable to determine combined_order_id');
                        Session::put('payment_error', translate('Order information not found.'));
                        return redirect()->route('payment.failed');
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
             
             // Fallback: try to redirect with order ID if available
             if (isset($input['oid'])) {
                 $parts = explode('-', $input['oid']);
                 if (count($parts) >= 2 && $parts[0] == 'CO') {
                     $combined_order_id = $parts[1];
                     return redirect()->route('order_confirmed_with_id', ['combined_order_id' => $combined_order_id]);
                 }
             }
             
             return redirect()->route('order_confirmed');
        } else {
            // Restore session if lost
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
            $errorMsg = $input['ErrMsg'] ?? translate("Your payment was not successful.");
            Session::put('payment_error', $errorMsg);
            return redirect()->route('payment.failed');
        }
    }

    public function fail(Request $request)
    {
        $input = $request->all();
        $storeKey = config('cmi.secret_key');
        
        // Verify Hash to prevent session manipulation
        if (isset($input['HASH'])) {
            $actualHash = $this->generateHash($input, $storeKey);
            if ($input['HASH'] === $actualHash) {
                // Restore session if lost and hash is valid
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
            } else {
                Log::warning('CMI Fail Route: Invalid Hash Attempt', ['ip' => $request->ip()]);
            }
        }
        
        $errorMsg = $input['ErrMsg'] ?? translate("Payment was cancelled or failed.");
        Session::put('payment_error', $errorMsg);
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
            
            // CMI V2.0 Rule: replace any character after "document" with a dot "."
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

    /**
     * Express Charge (Reference transaction) using a stored TransId Token
     */
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

            // Redirect the user to CMI gateway with the recurringTrxnRef populated.
            // For Reference transactions, CMI will bypass card entry and process it.
            return view('frontend.payment.cmi', compact('data', 'actionUrl'));

        } catch (\Exception $e) {
            Log::error('CMI Express Charge Error: ' . $e->getMessage());
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

<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaytmController extends Controller
{
    public function pay(Request $request)
    {
        // Paytm payment logic
        return \Illuminate\Support\Facades\Response::json(['message' => 'Paytm payment initiated']);
    }

    public function callback(Request $request)
    {
        // Paytm callback logic
        return \Illuminate\Support\Facades\Response::json(['message' => 'Paytm payment callback']);
    }
}

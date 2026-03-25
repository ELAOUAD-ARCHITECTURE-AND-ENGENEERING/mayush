<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PhonepeController extends Controller
{
    public function pay(Request $request)
    {
        // Phonepe payment logic
        return \Illuminate\Support\Facades\Response::json(['message' => 'Phonepe payment initiated']);
    }

    public function callback(Request $request)
    {
        // Phonepe callback logic
        return \Illuminate\Support\Facades\Response::json(['message' => 'Phonepe payment callback']);
    }
}

<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KhaltiController extends Controller
{
    public function pay(Request $request)
    {
        // Khalti payment logic
        return \Illuminate\Support\Facades\Response::json(['message' => 'Khalti payment initiated']);
    }

    public function verify(Request $request)
    {
        // Khalti verification logic
        return \Illuminate\Support\Facades\Response::json(['message' => 'Khalti payment verification']);
    }
}

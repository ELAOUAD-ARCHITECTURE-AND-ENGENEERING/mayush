<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GSTController extends Controller
{
    /**
     * Display GST dashboard
     */
    public function index()
    {
        return view('seller.gst.index');
    }

    /**
     * Update GST settings
     */
    public function update(Request $request)
    {
        // GST settings update logic
        return response()->json(['status' => 'success', 'message' => 'GST settings updated']);
    }
}
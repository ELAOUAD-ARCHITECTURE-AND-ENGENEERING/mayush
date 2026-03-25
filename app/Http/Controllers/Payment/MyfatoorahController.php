<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V2\MyfatoorahController as ApiMyfatoorahController;
use Illuminate\Http\Request;

class MyfatoorahController extends Controller
{
    /**
     * Handle MyFatoorah callback
     */
    public function callback(Request $request)
    {
        // Delegate to the API controller
        $apiController = new ApiMyfatoorahController();
        return $apiController->callback($request);
    }
}
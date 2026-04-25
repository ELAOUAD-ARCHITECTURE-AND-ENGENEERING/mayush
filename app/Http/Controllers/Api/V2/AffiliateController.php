<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\AffiliateConfig;

class AffiliateController extends Controller
{
    public function config()
    {
        return response()->json([
            'result' => true,
            'data' => AffiliateConfig::all(),
        ]);
    }
}

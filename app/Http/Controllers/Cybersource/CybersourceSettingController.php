<?php

namespace App\Http\Controllers\Cybersource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CybersourceSettingController extends Controller
{
    /**
     * Display CyberSource configuration page
     */
    public function configuration()
    {
        return view('backend.setup_configurations.cybersource_configuration');
    }
}
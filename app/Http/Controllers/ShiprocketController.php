<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShiprocketController extends Controller
{
    public function index()
    {
        return 'Stub';
    }
    public function store(Request $request)
    {
        return 'Stub';
    }
    public function update(Request $request)
    {
        return 'Stub';
    }
    public function createOrderShiprocket(Request $request) { return 'Stub'; }
    public function getCouriers(Request $request) { return 'Stub'; }
    public function assignAWB(Request $request) { return 'Stub'; }
    public function downloadLabel($order) { return 'Stub'; }
    public function downloadManifest($order) { return 'Stub'; }
    public function requestPickup(Request $request) { return 'Stub'; }
    public function deliveryStatus() { return 'Stub'; }
    public function orderStatus() { return 'Stub'; }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PathaoController extends Controller
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
    public function createOrderPathao(Request $request) { return 'Stub'; }
    public function deliveryStatus() { return 'Stub'; }
    public function getCities() { return 'Stub'; }
    public function getZones($city_id) { return 'Stub'; }
    public function getAreas($zone_id) { return 'Stub'; }
}

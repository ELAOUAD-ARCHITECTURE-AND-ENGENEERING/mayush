<?php

/*
|--------------------------------------------------------------------------
| GST Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\GSTController;

//Admin
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){
    Route::controller(GSTController::class)->group(function () {
        Route::get('/gst-unverified-sellers', 'index')->name('gst.unverified.sellers');
        Route::post('/gst-verify-seller', 'store')->name('gst.verify.seller');
        Route::get('/product-hsn-gst-assign', 'index')->name('products.hsn-gst.assigns');
        Route::get('/wholesale-product-hsn-gst-assign', 'index')->name('products.wholesale-hsn-gst.assigns');
        Route::get('/auction-product-hsn-gst-assign', 'index')->name('products.auction-hsn-gst.assigns');
        Route::get('/preorder-product-hsn-gst-assign', 'index')->name('products.preorder-hsn-gst.assigns');
        Route::post('/products-hsn-gst-single-update', 'update')->name('products.single-hsn-gst.update');
        Route::post('/bulk-product-gst-assign', 'update')->name('products.bulk-product-gst-assign');
        Route::get('/products/gst/products/{type}', 'index')->name('products.gst.filter');
    });
});

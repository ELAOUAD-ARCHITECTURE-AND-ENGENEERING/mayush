<?php

/*
|--------------------------------------------------------------------------
| B2B Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\WholesaleProductController;

//Admin

Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){   
    Route::controller(WholesaleProductController::class)->group(function () {
        Route::get('/wholesale/all-products', 'index')->name('wholesale_products.all');
        Route::get('/wholesale/inhouse-products', 'index')->name('wholesale_products.in_house');
        Route::get('/wholesale/seller-products', 'index')->name('wholesale_products.seller');

        Route::get('/wholesale-product/create', 'index')->name('wholesale_product_create.admin');
        Route::post('/wholesale-product/store', 'store')->name('wholesale_product_store.admin');
        Route::get('/wholesale-product/{id}/edit', 'index')->name('wholesale_product_edit.admin');
        Route::post('/wholesale-product/update/{id}', 'update')->name('wholesale_product_update.admin');
        Route::get('/wholesale-product/destroy/{id}', 'destroy')->name('wholesale_product_destroy.admin');
    });
});

Route::group(['prefix' => 'seller', 'middleware' => ['seller', 'verified', 'user']], function() {
    Route::controller(WholesaleProductController::class)->group(function () {
        Route::get('/wholesale-products', 'index')->name('seller.wholesale_products_list');

        Route::get('/wholesale-product/create', 'index')->name('wholesale_product_create.seller');
        Route::post('/wholesale-product/store', 'store')->name('wholesale_product_store.seller');
        Route::get('/wholesale-products/{id}/edit', 'index')->name('wholesale_product_edit.seller');
        Route::post('/wholesale-product/update/{id}', 'update')->name('wholesale_product_update.seller');
        Route::get('/wholesale-product/destroy/{id}', 'destroy')->name('wholesale_product_destroy.seller');
    });
});
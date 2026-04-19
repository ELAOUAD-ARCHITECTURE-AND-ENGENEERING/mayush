<?php

use App\Http\Controllers\Payment\CmiController;
use App\Http\Middleware\CmiIpWhitelist;

Route::controller(CmiController::class)->group(function () {
    Route::get('/cmi/pay', 'pay')->middleware('throttle:5,1')->name('cmi.pay');
    
    // Apply IP Whitelisting and Rate Limiting (throttle:60 requests per minute per IP)
    Route::post('/cmi/callback', 'callback')
        ->middleware(['throttle:60,1', CmiIpWhitelist::class])
        ->name('cmi.callback');
        
    Route::match(['get', 'post'], '/cmi/success', 'success')->name('cmi.success');
    Route::match(['get', 'post'], '/cmi/fail', 'fail')->name('cmi.fail');
});

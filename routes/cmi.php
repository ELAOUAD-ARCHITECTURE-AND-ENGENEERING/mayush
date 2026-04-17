<?php

use App\Http\Controllers\Payment\CmiController;

Route::controller(CmiController::class)->group(function () {
    Route::get('/cmi/pay', 'pay')->middleware('throttle:5,1')->name('cmi.pay');
    Route::post('/cmi/callback', 'callback')->name('cmi.callback');
    Route::match(['get', 'post'], '/cmi/success', 'success')->name('cmi.success');
    Route::match(['get', 'post'], '/cmi/fail', 'fail')->name('cmi.fail');
});

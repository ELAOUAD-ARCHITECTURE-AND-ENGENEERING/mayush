<?php

use Illuminate\Support\Facades\Route;
use Mayush\Shipping\Onessta\Http\Controllers\AdminController;

Route::prefix('admin/shipping/onessta')->name('onessta.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/shipments', [AdminController::class, 'shipments'])->name('shipments');
    Route::post('/sync-cities', [AdminController::class, 'syncCities'])->name('sync-cities');
    Route::post('/sync-pickup-cities', [AdminController::class, 'syncPickupCities'])->name('sync-pickup-cities');
    Route::post('/poll-tracking', [AdminController::class, 'pollTracking'])->name('poll-tracking');
    Route::get('/validate-credentials', [AdminController::class, 'validateCredentials'])->name('validate-credentials');
});

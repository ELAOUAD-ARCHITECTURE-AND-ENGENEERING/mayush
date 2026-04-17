<?php

use Illuminate\Support\Facades\Route;
use Mayush\Shipping\Onessta\Http\Controllers\WebhookController;

Route::post('/onessta', [WebhookController::class, 'handle'])
    ->name('onessta.webhook')
    ->withoutMiddleware(['web', 'auth', 'admin']);

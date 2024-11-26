<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'payment-ptp']);
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
});

Route::group(['prefix' => '/payments/gateway', 'as' => 'payments.gateway.'], function () {
    Route::post('/init', [PaymentController::class, 'init']);
    Route::post('/antilopayCallback', [PaymentController::class, 'antilopayCallback']);
    Route::get('/digisellerCallback', [PaymentController::class, 'digisellerCallback']);
});

Route::any('/bot/webhook/options', [NotificationController::class, 'saveNotificationBotWebhook']);

require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Services\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return response()->json(['service' => 'payment-ptp']);
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
});

Route::middleware('auth')->group(function () {
    Route::get('/message', [AccountController::class, 'message'])->name('message');
    Route::post('/saveOptions', [AccountController::class, 'saveOptionsNotification'])->name('saveOptions');
    Route::post('/saveBlacklist', [AccountController::class, 'saveBlacklist'])->name('saveBlacklist');
    Route::post('/deleteBlacklist', [AccountController::class, 'deleteBlacklist'])->name('deleteBlacklist');
});

Route::group(['prefix' => '/payments/gateway', 'as' => 'payments.gateway.'], function () {
    Route::post('/init', [PaymentController::class, 'init']);
    Route::post('/antilopayCallback', [PaymentController::class, 'antilopayCallback']);
    Route::get('/digisellerCallback', [PaymentController::class, 'digisellerCallback']);
});

Route::post('/bot/webhook/options', [NotificationController::class, 'saveNotificationBotWebhook']);

require __DIR__ . '/auth.php';

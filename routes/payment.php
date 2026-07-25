<?php

use App\Http\Controllers\Payment\PaymentChannelController;
use App\Http\Controllers\Payment\PaymentTransactionController;
use App\Http\Controllers\Payment\TriPayWebhookController;
use Illuminate\Support\Facades\Route;

// Public Callback Endpoint from TriPay Server (No Auth CSRF / CSRF Excluded)
Route::post('/api/payment/tripay/webhook', [TriPayWebhookController::class, 'handle'])->name('payment.tripay.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/payment/select/{orderNumber}', [PaymentChannelController::class, 'select'])->name('payment.select');
    Route::get('/api/payment/channels', [PaymentChannelController::class, 'getChannels'])->name('payment.channels');
    Route::post('/api/payment/process', [PaymentTransactionController::class, 'store'])->name('payment.process');
    Route::get('/payment/detail/{reference}', [PaymentTransactionController::class, 'show'])->name('payment.show');
});

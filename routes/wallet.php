<?php

use App\Http\Controllers\Publisher\PublisherWalletController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/publisher/wallet', [PublisherWalletController::class, 'index'])->name('publisher.wallet');
    Route::post('/publisher/wallet/withdraw', [PublisherWalletController::class, 'withdraw'])->name('publisher.wallet.withdraw');
});

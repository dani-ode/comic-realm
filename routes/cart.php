<?php

use App\Http\Controllers\Cart\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/api/cart/summary', [CartController::class, 'getSummary'])->name('cart.summary');
    Route::post('/api/cart/items', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/api/cart/items/{chapterId}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/api/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

<?php

use App\Http\Controllers\Entitlement\EntitlementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/library', [EntitlementController::class, 'index'])->name('library.index');
});

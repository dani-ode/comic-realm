<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPublisherController;
use App\Http\Controllers\Admin\AdminTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/publishers', [AdminPublisherController::class, 'index'])->name('admin.publishers.index');
    Route::post('/admin/publishers/{id}/approve', [AdminPublisherController::class, 'approve'])->name('admin.publishers.approve');
    Route::post('/admin/publishers/{id}/reject', [AdminPublisherController::class, 'reject'])->name('admin.publishers.reject');

    Route::get('/admin/transactions', [AdminTransactionController::class, 'index'])->name('admin.transactions.index');
    Route::post('/admin/withdrawals/{id}/approve', [AdminTransactionController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
});

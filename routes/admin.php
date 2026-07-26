<?php

use App\Http\Controllers\Admin\AdminChapterController;
use App\Http\Controllers\Admin\AdminComicController;
use App\Http\Controllers\Admin\AdminCommentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPublisherController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Studio / Publisher Management
    Route::get('/admin/publishers', [AdminPublisherController::class, 'index'])->name('admin.publishers.index');
    Route::get('/admin/publishers/{id}', [AdminPublisherController::class, 'show'])->name('admin.publishers.show');
    Route::post('/admin/publishers/{id}/approve', [AdminPublisherController::class, 'approve'])->name('admin.publishers.approve');
    Route::post('/admin/publishers/{id}/reject', [AdminPublisherController::class, 'reject'])->name('admin.publishers.reject');
    Route::post('/admin/publishers/{id}/block', [AdminPublisherController::class, 'block'])->name('admin.publishers.block');
    Route::post('/admin/publishers/{id}/unblock', [AdminPublisherController::class, 'unblock'])->name('admin.publishers.unblock');

    // Comic Management (Without Create function)
    Route::get('/admin/comics', [AdminComicController::class, 'index'])->name('admin.comics.index');
    Route::get('/admin/comics/{id}/edit', [AdminComicController::class, 'edit'])->name('admin.comics.edit');
    Route::post('/admin/comics/{id}/update', [AdminComicController::class, 'update'])->name('admin.comics.update');
    Route::delete('/admin/comics/{id}', [AdminComicController::class, 'destroy'])->name('admin.comics.destroy');

    // Chapter Management (Without Create function)
    Route::get('/admin/chapters', [AdminChapterController::class, 'index'])->name('admin.chapters.index');
    Route::post('/admin/chapters/{id}/update', [AdminChapterController::class, 'update'])->name('admin.chapters.update');
    Route::delete('/admin/chapters/{id}', [AdminChapterController::class, 'destroy'])->name('admin.chapters.destroy');

    // Comment Management (Without Create function)
    Route::get('/admin/comments', [AdminCommentController::class, 'index'])->name('admin.comments.index');
    Route::post('/admin/comments/{id}/toggle-status', [AdminCommentController::class, 'toggleStatus'])->name('admin.comments.toggle-status');
    Route::delete('/admin/comments/{id}', [AdminCommentController::class, 'destroy'])->name('admin.comments.destroy');

    // User Management
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('/admin/users/{id}/change-role', [AdminUserController::class, 'changeRole'])->name('admin.users.change-role');

    // Transactions & Withdrawals Management
    Route::get('/admin/transactions', [AdminTransactionController::class, 'index'])->name('admin.transactions.index');
    Route::post('/admin/withdrawals/{id}/approve', [AdminTransactionController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
    Route::post('/admin/withdrawals/{id}/reject', [AdminTransactionController::class, 'rejectWithdrawal'])->name('admin.withdrawals.reject');
});

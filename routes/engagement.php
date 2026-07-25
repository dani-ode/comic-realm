<?php

use App\Http\Controllers\Engagement\BookmarkController;
use App\Http\Controllers\Engagement\CommentController;
use App\Http\Controllers\Engagement\RatingController;
use Illuminate\Support\Facades\Route;

Route::get('/api/comments', [CommentController::class, 'index'])->name('comments.index');

Route::middleware('auth')->group(function () {
    Route::post('/api/bookmarks/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::post('/api/ratings', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/api/comments', [CommentController::class, 'store'])->name('comments.store');
});

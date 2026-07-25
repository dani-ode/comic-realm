<?php

use App\Http\Controllers\Reader\ReaderController;
use App\Http\Controllers\Reader\ReadingProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/read/{comicSlug}/{chapterNumber}', [ReaderController::class, 'show'])->name('reader.show');

Route::middleware('auth')->group(function () {
    Route::post('/api/reader/progress', [ReadingProgressController::class, 'store'])->name('reader.progress.store');
});

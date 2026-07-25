<?php

use App\Http\Controllers\Publisher\PublisherApplicationController;
use App\Http\Controllers\Publisher\PublisherChapterController;
use App\Http\Controllers\Publisher\PublisherComicController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/publisher/apply', [PublisherApplicationController::class, 'create'])->name('publisher.apply');
    Route::post('/publisher/apply', [PublisherApplicationController::class, 'store']);
    Route::post('/publisher/profile/update', [PublisherApplicationController::class, 'update'])->name('publisher.profile.update');

    Route::middleware('auth')->group(function () {
        Route::get('/publisher/dashboard', [PublisherComicController::class, 'dashboard'])->name('publisher.dashboard');
        Route::get('/publisher/comics/create', [PublisherComicController::class, 'create'])->name('publisher.comics.create');
        Route::post('/publisher/comics', [PublisherComicController::class, 'store'])->name('publisher.comics.store');

        Route::get('/publisher/comics/{comicId}/chapters/create', [PublisherChapterController::class, 'create'])->name('publisher.chapters.create');
        Route::post('/publisher/comics/{comicId}/chapters', [PublisherChapterController::class, 'store'])->name('publisher.chapters.store');
        Route::post('/api/publisher/chapters/pages', [PublisherChapterController::class, 'uploadPages'])->name('publisher.chapters.pages');
    });
});

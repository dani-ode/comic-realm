<?php

use App\Http\Controllers\Publisher\PublisherApplicationController;
use App\Http\Controllers\Publisher\PublisherChapterController;
use App\Http\Controllers\Publisher\PublisherComicController;
use App\Http\Controllers\Publisher\PublisherGenreController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/publisher/apply', [PublisherApplicationController::class, 'create'])->name('publisher.apply');
    Route::post('/publisher/apply', [PublisherApplicationController::class, 'store']);
    Route::post('/publisher/profile/update', [PublisherApplicationController::class, 'update'])->name('publisher.profile.update');

    Route::middleware('auth')->group(function () {
        Route::get('/publisher/dashboard', [PublisherComicController::class, 'dashboard'])->name('publisher.dashboard');
        Route::get('/publisher/profile', [PublisherApplicationController::class, 'editProfile'])->name('publisher.profile.edit');
        Route::post('/publisher/genres', [PublisherGenreController::class, 'store'])->name('publisher.genres.store');
        Route::get('/publisher/comics', [PublisherComicController::class, 'index'])->name('publisher.comics.index');
        Route::get('/publisher/comics/create', [PublisherComicController::class, 'create'])->name('publisher.comics.create');
        Route::post('/publisher/comics', [PublisherComicController::class, 'store'])->name('publisher.comics.store');
        Route::get('/publisher/comics/{id}/edit', [PublisherComicController::class, 'edit'])->name('publisher.comics.edit');
        Route::post('/publisher/comics/{id}/update', [PublisherComicController::class, 'update'])->name('publisher.comics.update');
        Route::delete('/publisher/comics/{id}', [PublisherComicController::class, 'destroy'])->name('publisher.comics.destroy');

        Route::get('/publisher/comics/{comicId}/chapters/create', [PublisherChapterController::class, 'create'])->name('publisher.chapters.create');
        Route::post('/publisher/comics/{comicId}/chapters', [PublisherChapterController::class, 'store'])->name('publisher.chapters.store');
        Route::get('/publisher/comics/{comicId}/chapters/{chapterId}/edit', [PublisherChapterController::class, 'edit'])->name('publisher.chapters.edit');
        Route::post('/publisher/comics/{comicId}/chapters/{chapterId}/update', [PublisherChapterController::class, 'update'])->name('publisher.chapters.update');
        Route::delete('/publisher/comics/{comicId}/chapters/{chapterId}', [PublisherChapterController::class, 'destroy'])->name('publisher.chapters.destroy');

        Route::post('/api/publisher/chapters/pages', [PublisherChapterController::class, 'uploadPages'])->name('publisher.chapters.pages');

        Route::post('/publisher/comics/{comicId}/chapters/{chapterId}/pages/upload', [PublisherChapterController::class, 'uploadPagesRealtime'])->name('publisher.chapters.pages.upload');
        Route::delete('/publisher/comics/{comicId}/chapters/{chapterId}/pages/{pageId}', [PublisherChapterController::class, 'deletePageRealtime'])->name('publisher.chapters.pages.delete');
        Route::post('/publisher/comics/{comicId}/chapters/{chapterId}/pages/reorder', [PublisherChapterController::class, 'reorderPagesRealtime'])->name('publisher.chapters.pages.reorder');
    });
});

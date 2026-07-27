<?php

use App\Http\Controllers\Public\ComicController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\StudioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/comics', [ComicController::class, 'index'])->name('comics.index');
Route::get('/comics/{slug}', [ComicController::class, 'show'])->name('comics.show');
Route::get('/studios', [StudioController::class, 'index'])->name('studios.index');
Route::get('/studios/{idOrSlug}', [StudioController::class, 'show'])->name('studios.show');

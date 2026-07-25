<?php

namespace App\Http\Controllers\Public;

use App\Domain\Comic\Models\Genre;
use App\Domain\Comic\Queries\ComicQuery;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class HomeController extends Controller
{
    public function index(ComicQuery $comicQuery): InertiaResponse
    {
        return Inertia::render('Public/Home', [
            'featuredComics' => $comicQuery->getFeaturedComics(),
            'popularComics' => $comicQuery->getPopularComics(),
            'latestComics' => $comicQuery->getLatestUpdates(),
            'genres' => Genre::where('is_active', true)->get(['id', 'name', 'slug']),
        ]);
    }
}

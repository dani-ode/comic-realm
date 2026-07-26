<?php

namespace App\Http\Controllers\Public;

use App\Domain\Cart\Models\Cart;
use App\Domain\Comic\Models\Comic;
use App\Domain\Comic\Models\Genre;
use App\Domain\Comic\Queries\ComicQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ComicController extends Controller
{
    public function index(Request $request, ComicQuery $comicQuery): InertiaResponse
    {
        $filters = $request->only(['search', 'genre', 'status', 'sort']);

        return Inertia::render('Public/Comics/Index', [
            'comics' => $comicQuery->getCatalogPaginator($filters),
            'filters' => $filters,
            'genres' => Genre::where('is_active', true)->get(['id', 'name', 'slug']),
        ]);
    }

    public function show(string $slug, Request $request): InertiaResponse
    {
        $comic = Comic::query()
            ->with([
                'genres',
                'publisher' => fn ($q) => $q->with('publisherProfile'),
                'publishedChapters' => fn ($q) => $q->orderBy('chapter_number', 'desc'),
            ])
            ->where('slug', $slug)
            ->where('publication_status', 'published')
            ->firstOrFail();

        $comic->increment('total_views');

        $user = $request->user();
        $unlockedChapterIds = [];
        $cartChapterIds = [];
        $isBookmarked = false;
        $userRating = 0;

        if ($user) {
            if (Schema::hasTable('entitlements')) {
                $unlockedChapterIds = DB::table('entitlements')
                    ->where('user_id', $user->id)
                    ->whereNull('revoked_at')
                    ->pluck('chapter_id')
                    ->toArray();
            }

            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                $cartChapterIds = $cart->items()->pluck('chapter_id')->toArray();
            }

            if (Schema::hasTable('bookmarks')) {
                $isBookmarked = DB::table('bookmarks')
                    ->where('user_id', $user->id)
                    ->where('comic_id', $comic->id)
                    ->exists();
            }

            if (Schema::hasTable('ratings')) {
                $ratingRecord = DB::table('ratings')
                    ->where('user_id', $user->id)
                    ->where('comic_id', $comic->id)
                    ->first();

                if ($ratingRecord) {
                    $userRating = (int) $ratingRecord->rating;
                }
            }
        }

        return Inertia::render('Public/Comics/Show', [
            'comic' => $comic,
            'unlockedChapterIds' => $unlockedChapterIds,
            'cartChapterIds' => $cartChapterIds,
            'isBookmarked' => $isBookmarked,
            'userRating' => $userRating,
        ]);
    }
}

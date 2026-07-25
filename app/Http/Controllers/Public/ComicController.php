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
                'publisher',
                'publishedChapters' => fn ($q) => $q->orderBy('chapter_number', 'desc'),
            ])
            ->where('slug', $slug)
            ->where('publication_status', 'published')
            ->firstOrFail();

        $user = $request->user();
        $unlockedChapterIds = [];
        $cartChapterIds = [];

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
        }

        return Inertia::render('Public/Comics/Show', [
            'comic' => $comic,
            'unlockedChapterIds' => $unlockedChapterIds,
            'cartChapterIds' => $cartChapterIds,
        ]);
    }
}

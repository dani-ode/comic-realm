<?php

namespace App\Http\Controllers\Public;

use App\Domain\Comic\Models\Comic;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StudioController extends Controller
{
    public function show(string $idOrSlug): InertiaResponse
    {
        $publisher = User::query()
            ->where('role', 'publisher')
            ->where(function ($query) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $query->where('id', (int) $idOrSlug);
                } else {
                    $query->whereHas('publisherProfile', fn ($q) => $q->where('slug', $idOrSlug));
                }
            })
            ->with('publisherProfile')
            ->firstOrFail();

        $comics = Comic::query()
            ->where('publisher_id', $publisher->id)
            ->where('publication_status', 'published')
            ->with(['genres', 'publisher' => fn ($q) => $q->with('publisherProfile')])
            ->latest('published_at')
            ->paginate(12);

        $totalViews = Comic::where('publisher_id', $publisher->id)
            ->where('publication_status', 'published')
            ->sum('total_views');

        $totalRatings = Comic::where('publisher_id', $publisher->id)
            ->where('publication_status', 'published')
            ->sum('total_ratings');

        $averageRating = Comic::where('publisher_id', $publisher->id)
            ->where('publication_status', 'published')
            ->where('total_ratings', '>', 0)
            ->avg('rating_average') ?: 0.0;

        return Inertia::render('Public/Studios/Show', [
            'publisher' => [
                'id' => $publisher->id,
                'name' => $publisher->name,
                'username' => $publisher->username,
                'brand_name' => $publisher->publisherProfile?->brand_name ?? $publisher->name,
                'slug' => $publisher->publisherProfile?->slug ?? (string) $publisher->id,
                'bio' => $publisher->publisherProfile?->bio,
                'logo' => $publisher->publisherProfile?->logo,
                'banner' => $publisher->publisherProfile?->banner,
                'created_at' => $publisher->created_at ? $publisher->created_at->format('M Y') : null,
            ],
            'comics' => $comics,
            'totalViews' => (int) $totalViews,
            'totalRatings' => (int) $totalRatings,
            'averageRating' => round((float) $averageRating, 2),
        ]);
    }
}

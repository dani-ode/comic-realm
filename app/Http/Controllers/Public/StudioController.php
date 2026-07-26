<?php

namespace App\Http\Controllers\Public;

use App\Domain\Comic\Models\Comic;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StudioController extends Controller
{
    public function show(int $id): InertiaResponse
    {
        $publisher = User::query()
            ->where('id', $id)
            ->where('role', 'publisher')
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

        return Inertia::render('Public/Studios/Show', [
            'publisher' => [
                'id' => $publisher->id,
                'name' => $publisher->name,
                'username' => $publisher->username,
                'brand_name' => $publisher->publisherProfile?->brand_name ?? $publisher->name,
                'bio' => $publisher->publisherProfile?->bio,
                'logo' => $publisher->publisherProfile?->logo,
                'banner' => $publisher->publisherProfile?->banner,
                'created_at' => $publisher->created_at ? $publisher->created_at->format('M Y') : null,
            ],
            'comics' => $comics,
            'totalViews' => (int) $totalViews,
        ]);
    }
}

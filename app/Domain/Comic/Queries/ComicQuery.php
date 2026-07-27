<?php

namespace App\Domain\Comic\Queries;

use App\Domain\Comic\Models\Comic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ComicQuery
{
    public function getFeaturedComics(int $limit = 6): Collection
    {
        $featured = Comic::query()
            ->with(['genres', 'publisher'])
            ->where('publication_status', 'published')
            ->where('is_featured', true)
            ->latest('featured_at')
            ->take($limit)
            ->get();

        if ($featured->count() < $limit) {
            $remainingCount = $limit - $featured->count();
            $existingIds = $featured->pluck('id')->toArray();

            $fillers = Comic::query()
                ->with(['genres', 'publisher'])
                ->where('publication_status', 'published')
                ->whereNotIn('id', $existingIds)
                ->orderByDesc('total_views')
                ->take($remainingCount)
                ->get();

            $featured = $featured->concat($fillers);
        }

        return $featured;
    }

    public function getPopularComics(int $limit = 6): Collection
    {
        return Comic::query()
            ->with(['genres', 'publisher'])
            ->where('publication_status', 'published')
            ->orderByDesc('total_views')
            ->take($limit)
            ->get();
    }

    public function getLatestUpdates(int $limit = 10): Collection
    {
        return Comic::query()
            ->with(['genres', 'publisher', 'chapters' => fn ($q) => $q->latest('published_at')])
            ->where('publication_status', 'published')
            ->latest('published_at')
            ->latest('id')
            ->take($limit)
            ->get();
    }

    public function getCatalogPaginator(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $sort = isset($filters['sort']) && is_string($filters['sort']) ? $filters['sort'] : 'latest';

        return Comic::query()
            ->with(['genres', 'publisher'])
            ->where('publication_status', 'published')
            ->when(! empty($filters['search']), function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where(function (Builder $q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author_name', 'like', "%{$search}%")
                        ->orWhere('artist_name', 'like', "%{$search}%");
                });
            })
            ->when(! empty($filters['genre']), function (Builder $query) use ($filters) {
                $query->whereHas('genres', fn (Builder $q) => $q->where('slug', $filters['genre']));
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->tap(function (Builder $query) use ($sort) {
                match ($sort) {
                    'popular' => $query->orderByDesc('total_views')->latest('id'),
                    'rating' => $query->orderByDesc('rating_average')->latest('id'),
                    'oldest' => $query->oldest('published_at')->oldest('id'),
                    default => $query->latest('published_at')->latest('id'),
                };
            })
            ->paginate($perPage)
            ->withQueryString();
    }
}

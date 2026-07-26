<?php

namespace App\Domain\Comic\Queries;

use App\Domain\Comic\Models\Comic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ComicQuery
{
    public function getFeaturedComics(int $limit = 6): Collection
    {
        return Comic::query()
            ->with(['genres', 'publisher'])
            ->where('publication_status', 'published')
            ->where('is_featured', true)
            ->latest('featured_at')
            ->take($limit)
            ->get();
    }

    public function getPopularComics(int $limit = 10): Collection
    {
        return Comic::query()
            ->with(['genres', 'publisher'])
            ->where('publication_status', 'published')
            ->orderByDesc('total_views')
            ->orderByDesc('rating_average')
            ->take($limit)
            ->get();
    }

    public function getLatestUpdates(int $limit = 10): Collection
    {
        return Comic::query()
            ->with(['genres', 'publisher', 'publishedChapters' => fn ($q) => $q->latest()->take(2)])
            ->where('publication_status', 'published')
            ->latest('published_at')
            ->take($limit)
            ->get();
    }

    public function getCatalogPaginator(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
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
            ->when(! empty($filters['sort']), function (Builder $query) use ($filters) {
                match ($filters['sort']) {
                    'popular' => $query->orderByDesc('total_views')->latest('id'),
                    'rating' => $query->orderByDesc('rating_average')->latest('id'),
                    'oldest' => $query->oldest('published_at')->oldest('id'),
                    'latest' => $query->latest('published_at')->latest('id'),
                    default => $query->latest('published_at')->latest('id'),
                };
            }, fn (Builder $query) => $query->latest('published_at')->latest('id'))
            ->paginate($perPage)
            ->withQueryString();
    }
}

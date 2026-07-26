<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Comic\Models\Comic;
use App\Domain\Engagement\DTOs\RateComicData;
use App\Domain\Engagement\Models\Rating;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class RateComic
{
    public function execute(User $user, RateComicData $data): Rating
    {
        return DB::transaction(function () use ($user, $data) {
            $comic = Comic::findOrFail($data->comic_id);

            $rating = Rating::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'comic_id' => $comic->id,
                ],
                [
                    'rating' => $data->rating,
                    'review_text' => $data->review_text,
                ]
            );

            // Recalculate average rating & total rating count
            $totalRatings = Rating::where('comic_id', $comic->id)->count();
            $averageRating = Rating::where('comic_id', $comic->id)->avg('rating') ?: 0.0;

            $comic->update([
                'total_ratings' => $totalRatings,
                'rating_average' => round($averageRating, 2),
            ]);

            return $rating;
        });
    }

    public function removeRating(User $user, int $comicId): Comic
    {
        return DB::transaction(function () use ($user, $comicId) {
            $comic = Comic::findOrFail($comicId);

            Rating::where('user_id', $user->id)
                ->where('comic_id', $comic->id)
                ->delete();

            // Recalculate average rating & total rating count
            $totalRatings = Rating::where('comic_id', $comic->id)->count();
            $averageRating = Rating::where('comic_id', $comic->id)->avg('rating') ?: 0.0;

            $comic->update([
                'total_ratings' => $totalRatings,
                'rating_average' => round($averageRating, 2),
            ]);

            return $comic;
        });
    }
}

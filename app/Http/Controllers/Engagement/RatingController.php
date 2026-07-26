<?php

namespace App\Http\Controllers\Engagement;

use App\Domain\Comic\Models\Comic;
use App\Domain\Engagement\Actions\RateComic;
use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\RateComicRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(RateComicRequest $request, RateComic $rateComic): JsonResponse
    {
        $rating = $rateComic->execute($request->user(), $request->toDTO());
        $comic = Comic::find($request->input('comic_id'));

        return response()->json([
            'success' => true,
            'data' => $rating,
            'user_rating' => $rating->rating,
            'rating_average' => $comic ? $comic->rating_average : 0.0,
            'total_ratings' => $comic ? $comic->total_ratings : 0,
            'message' => 'Rating & ulasan Anda berhasil disimpan.',
        ]);
    }

    public function cancel(Request $request, RateComic $rateComic): JsonResponse
    {
        $request->validate([
            'comic_id' => ['required', 'integer', 'exists:comics,id'],
        ]);

        $comic = $rateComic->removeRating($request->user(), (int) $request->input('comic_id'));

        return response()->json([
            'success' => true,
            'user_rating' => 0,
            'rating_average' => $comic ? $comic->rating_average : 0.0,
            'total_ratings' => $comic ? $comic->total_ratings : 0,
            'message' => 'Rating berhasil dibatalkan.',
        ]);
    }
}

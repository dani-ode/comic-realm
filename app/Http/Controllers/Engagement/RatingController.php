<?php

namespace App\Http\Controllers\Engagement;

use App\Domain\Engagement\Actions\RateComic;
use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\RateComicRequest;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    public function store(RateComicRequest $request, RateComic $rateComic): JsonResponse
    {
        $rating = $rateComic->execute($request->user(), $request->toDTO());

        return response()->json([
            'success' => true,
            'data' => $rating,
            'message' => 'Rating & ulasan Anda berhasil disimpan.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Reader;

use App\Domain\Reading\Actions\SaveReadingProgress;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reader\SaveProgressRequest;
use Illuminate\Http\JsonResponse;

class ReadingProgressController extends Controller
{
    public function store(SaveProgressRequest $request, SaveReadingProgress $saveProgress): JsonResponse
    {
        $progress = $saveProgress->execute($request->user(), $request->toDTO());

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }
}

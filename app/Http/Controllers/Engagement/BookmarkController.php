<?php

namespace App\Http\Controllers\Engagement;

use App\Domain\Engagement\Actions\ToggleBookmark;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function toggle(Request $request, ToggleBookmark $toggleBookmark): JsonResponse
    {
        $request->validate([
            'comic_id' => ['required', 'integer', 'exists:comics,id'],
        ]);

        $bookmarked = $toggleBookmark->execute($request->user(), (int) $request->input('comic_id'));

        return response()->json([
            'success' => true,
            'bookmarked' => $bookmarked,
            'message' => $bookmarked ? 'Komik berhasil ditambahkan ke bookmark.' : 'Bookmark berhasil dihapus.',
        ]);
    }
}

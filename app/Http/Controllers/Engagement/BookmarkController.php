<?php

namespace App\Http\Controllers\Engagement;

use App\Domain\Engagement\Actions\ToggleBookmark;
use App\Domain\Engagement\Models\Bookmark;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BookmarkController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $bookmarks = Bookmark::where('user_id', $request->user()->id)
            ->with(['comic' => function ($q) {
                $q->with(['genres', 'publisher']);
            }])
            ->latest()
            ->paginate(12);

        return Inertia::render('Bookmarks/Index', [
            'bookmarks' => $bookmarks,
        ]);
    }

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

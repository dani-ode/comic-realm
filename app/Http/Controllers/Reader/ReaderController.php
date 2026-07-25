<?php

namespace App\Http\Controllers\Reader;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Reading\Actions\TrackComicView;
use App\Domain\Reading\Models\ReadingProgress;
use App\Domain\Reading\Services\ReaderAccessService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;

class ReaderController extends Controller
{
    public function show(
        string $comicSlug,
        float $chapterNumber,
        Request $request,
        ReaderAccessService $accessService,
        TrackComicView $trackView
    ): InertiaResponse|RedirectResponse {
        $comic = Comic::where('slug', $comicSlug)->firstOrFail();

        $chapter = Chapter::with(['pages'])
            ->where('comic_id', $comic->id)
            ->where('chapter_number', $chapterNumber)
            ->where('status', 'published')
            ->firstOrFail();

        $user = $request->user();

        // 1. Pengecekan Hak Akses Membaca
        if (! $accessService->canRead($user, $chapter)) {
            return redirect()->route('comics.show', $comic->slug)->with('error', 'Silakan beli bab ini terlebih dahulu untuk membaca.');
        }

        // 2. Pencatatan View
        $trackView->execute($comic, $chapter, $user, $request);

        // 3. Ambil posisi bacaan terakhir jika user login
        $savedProgress = null;
        if ($user) {
            $savedProgress = ReadingProgress::where('user_id', $user->id)
                ->where('chapter_id', $chapter->id)
                ->first();
        }

        // 4. Bab Sebelumnya & Selanjutnya
        $prevChapter = Chapter::where('comic_id', $comic->id)
            ->where('status', 'published')
            ->where('chapter_number', '<', $chapter->chapter_number)
            ->orderBy('chapter_number', 'desc')
            ->first(['chapter_number', 'title', 'slug']);

        $nextChapter = Chapter::where('comic_id', $comic->id)
            ->where('status', 'published')
            ->where('chapter_number', '>', $chapter->chapter_number)
            ->orderBy('chapter_number', 'asc')
            ->first(['chapter_number', 'title', 'slug']);

        $allChapters = Chapter::where('comic_id', $comic->id)
            ->where('status', 'published')
            ->orderBy('chapter_number', 'asc')
            ->get(['id', 'chapter_number', 'title', 'is_free']);

        return Inertia::render('Reader/Show', [
            'comic' => $comic->only(['id', 'title', 'slug', 'cover_image']),
            'chapter' => $chapter,
            'prevChapter' => $prevChapter,
            'nextChapter' => $nextChapter,
            'allChapters' => $allChapters,
            'savedProgress' => $savedProgress,
        ]);
    }
}

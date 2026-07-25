<?php

namespace App\Http\Controllers\Publisher;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Publisher\Actions\UploadChapterPagesBatch;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publisher\UploadChapterPagesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublisherChapterController extends Controller
{
    public function create(int $comicId, Request $request): InertiaResponse|RedirectResponse
    {
        $user = $request->user();
        $profile = PublisherProfile::where('user_id', $user->id)->first();

        if (! $profile || ! $profile->isApproved()) {
            return redirect()->route('publisher.dashboard')->with('error', 'Studio Anda belum disetujui oleh admin. Anda belum dapat menerbitkan bab baru.');
        }

        $comic = Comic::where('id', $comicId)
            ->where('publisher_id', $user->id)
            ->firstOrFail();

        return Inertia::render('Publisher/Chapters/Create', [
            'comic' => $comic,
        ]);
    }

    public function store(int $comicId, Request $request, UploadChapterPagesBatch $uploadBatch): RedirectResponse
    {
        $user = $request->user();
        $profile = PublisherProfile::where('user_id', $user->id)->first();

        if (! $profile || ! $profile->isApproved()) {
            return redirect()->route('publisher.dashboard')->with('error', 'Studio Anda belum disetujui oleh admin. Anda belum dapat menerbitkan bab baru.');
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'chapter_number' => ['required', 'numeric', 'min:0.1'],
            'is_free' => ['required', 'boolean'],
            'price' => ['required_if:is_free,false', 'numeric', 'min:0'],
            'pages' => ['nullable', 'array'],
        ]);

        $comic = Comic::where('id', $comicId)
            ->where('publisher_id', $user->id)
            ->firstOrFail();

        $chapterNumber = (float) $request->input('chapter_number');
        $title = $request->input('title');

        $chapter = Chapter::create([
            'comic_id' => $comic->id,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $chapterNumber,
            'chapter_number' => $chapterNumber,
            'is_free' => (bool) $request->input('is_free'),
            'price' => $request->input('is_free') ? 0 : (int) $request->input('price'),
            'status' => 'published',
            'published_at' => now(),
        ]);

        if ($request->hasFile('pages')) {
            $uploadBatch->execute($chapter, $request->file('pages'));
        }

        return redirect()->route('publisher.dashboard')->with('success', "Bab {$chapterNumber} berhasil diterbitkan.");
    }

    public function uploadPages(UploadChapterPagesRequest $request, UploadChapterPagesBatch $uploadBatch): JsonResponse
    {
        $user = $request->user();
        $profile = PublisherProfile::where('user_id', $user->id)->first();

        if (! $profile || ! $profile->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Studio Anda belum disetujui oleh admin.',
            ], 403);
        }

        $chapter = Chapter::with('comic')
            ->where('id', $request->input('chapter_id'))
            ->whereHas('comic', fn ($q) => $q->where('publisher_id', $user->id))
            ->firstOrFail();

        $pages = $uploadBatch->execute($chapter, $request->file('pages'));

        return response()->json([
            'success' => true,
            'pages' => $pages,
            'message' => count($pages) . ' halaman gambar WebP berhasil diunggah.',
        ]);
    }
}

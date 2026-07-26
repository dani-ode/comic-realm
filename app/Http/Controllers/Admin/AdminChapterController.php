<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminChapterController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $comicId = $request->query('comic_id');
        $search = $request->query('search');

        $query = Chapter::with('comic')
            ->withCount('pages');

        if ($comicId) {
            $query->where('comic_id', $comicId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('comic', function ($cq) use ($search) {
                        $cq->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $chapters = $query->latest()->paginate(15)->withQueryString();
        $selectedComic = $comicId ? Comic::find($comicId) : null;
        $comics = Comic::get(['id', 'title']);

        return Inertia::render('Admin/Chapters/Index', [
            'chapters' => $chapters,
            'selectedComic' => $selectedComic,
            'comics' => $comics,
            'filters' => [
                'comic_id' => $comicId,
                'search' => $search,
            ],
        ]);
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'chapter_number' => ['required', 'numeric', 'min:0.1'],
            'is_free' => ['required', 'boolean'],
            'price' => ['required_if:is_free,false', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:published,draft'],
        ]);

        $chapter = Chapter::findOrFail($id);
        $chapter->update([
            'title' => $request->input('title'),
            'chapter_number' => (float) $request->input('chapter_number'),
            'is_free' => (bool) $request->input('is_free'),
            'price' => $request->input('is_free') ? 0 : (int) $request->input('price'),
            'status' => $request->input('status'),
        ]);

        return redirect()->back()->with('success', "Bab {$chapter->chapter_number} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return redirect()->back()->with('success', "Bab {$chapter->chapter_number} telah dihapus.");
    }
}

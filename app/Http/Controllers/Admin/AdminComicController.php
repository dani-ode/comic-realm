<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Comic\Models\Comic;
use App\Domain\Comic\Models\Genre;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

use App\Domain\Publisher\Models\PublisherProfile;

class AdminComicController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $status = $request->query('status');
        $pubStatus = $request->query('publication_status');
        $publisherId = $request->query('publisher_id');
        $search = $request->query('search');

        $query = Comic::with(['publisher', 'genres'])
            ->withCount('chapters');

        if ($status) {
            $query->where('status', $status);
        }

        if ($pubStatus) {
            $query->where('publication_status', $pubStatus);
        }

        if ($publisherId) {
            $query->where('publisher_id', $publisherId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%")
                    ->orWhere('artist_name', 'like', "%{$search}%");
            });
        }

        $comics = $query->latest()->paginate(15)->withQueryString();
        $genres = Genre::where('is_active', true)->get(['id', 'name']);
        $publishers = PublisherProfile::with('user:id,name,email')->get(['id', 'user_id', 'brand_name']);

        return Inertia::render('Admin/Comics/Index', [
            'comics' => $comics,
            'genres' => $genres,
            'publishers' => $publishers,
            'filters' => [
                'status' => $status,
                'publication_status' => $pubStatus,
                'publisher_id' => $publisherId,
                'search' => $search,
            ],
        ]);
    }

    public function edit(int $id): InertiaResponse
    {
        $comic = Comic::with(['genres', 'chapters'])->findOrFail($id);
        $genres = Genre::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Comics/Edit', [
            'comic' => $comic,
            'genres' => $genres,
        ]);
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'author_name' => ['nullable', 'string', 'max:150'],
            'artist_name' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string'],
            'publication_status' => ['required', 'string'],
            'genres' => ['nullable', 'array'],
        ]);

        $comic = Comic::findOrFail($id);
        $comic->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'cover_image' => $request->input('cover_image', $comic->cover_image),
            'author_name' => $request->input('author_name'),
            'artist_name' => $request->input('artist_name'),
            'status' => $request->input('status'),
            'publication_status' => $request->input('publication_status'),
        ]);

        if ($request->has('genres')) {
            $comic->genres()->sync($request->input('genres'));
        }

        return redirect()->route('admin.comics.index')->with('success', "Komik {$comic->title} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $comic = Comic::findOrFail($id);
        $comic->delete();

        return redirect()->back()->with('success', "Komik {$comic->title} telah dihapus.");
    }
}

<?php

namespace App\Http\Controllers\Publisher;

use App\Domain\Comic\Models\Comic;
use App\Domain\Comic\Models\Genre;
use App\Domain\Publisher\Models\PublisherProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublisherComicController extends Controller
{
    public function dashboard(Request $request): InertiaResponse|RedirectResponse
    {
        $user = $request->user();

        $profile = PublisherProfile::where('user_id', $user->id)->first();

        // Jika belum mengajukan studio sama sekali, arahkan otomatis ke /publisher/apply
        if (! $profile) {
            return redirect()->route('publisher.apply');
        }

        $comics = Comic::with(['genres', 'chapters'])
            ->where('publisher_id', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Publisher/Dashboard', [
            'profile' => $profile,
            'comics' => $comics,
        ]);
    }

    public function create(Request $request): InertiaResponse|RedirectResponse
    {
        $user = $request->user();
        $profile = PublisherProfile::where('user_id', $user->id)->first();

        if (! $profile || ! $profile->isApproved()) {
            return redirect()->route('publisher.dashboard')->with('error', 'Studio Anda belum disetujui oleh admin. Anda belum dapat membuat komik baru.');
        }

        $genres = Genre::where('is_active', true)->get(['id', 'name', 'slug']);

        return Inertia::render('Publisher/Comics/Create', [
            'genres' => $genres,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = PublisherProfile::where('user_id', $user->id)->first();

        if (! $profile || ! $profile->isApproved()) {
            return redirect()->route('publisher.dashboard')->with('error', 'Studio Anda belum disetujui oleh admin. Anda belum dapat membuat komik baru.');
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'cover_image' => ['required', 'string', 'max:500'],
            'author_name' => ['nullable', 'string', 'max:150'],
            'artist_name' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string'],
            'genres' => ['required', 'array', 'min:1'],
        ]);

        $title = $request->input('title');
        $slug = Str::slug($title) . '-' . rand(100, 999);

        $comic = Comic::create([
            'publisher_id' => $user->id,
            'title' => $title,
            'slug' => $slug,
            'description' => $request->input('description'),
            'cover_image' => $request->input('cover_image'),
            'author_name' => $request->input('author_name'),
            'artist_name' => $request->input('artist_name'),
            'status' => $request->input('status', 'ongoing'),
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        $comic->genres()->sync($request->input('genres'));

        return redirect()->route('publisher.dashboard')->with('success', 'Komik baru berhasil dibuat.');
    }

    public function edit(int $id, Request $request): InertiaResponse|RedirectResponse
    {
        $user = $request->user();
        $comic = Comic::with(['genres', 'chapters'])
            ->where('id', $id)
            ->where('publisher_id', $user->id)
            ->firstOrFail();

        $genres = Genre::where('is_active', true)->get(['id', 'name', 'slug']);

        return Inertia::render('Publisher/Comics/Edit', [
            'comic' => $comic,
            'genres' => $genres,
        ]);
    }

    public function update(int $id, Request $request): RedirectResponse
    {
        $user = $request->user();
        $comic = Comic::where('id', $id)
            ->where('publisher_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'author_name' => ['nullable', 'string', 'max:150'],
            'artist_name' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string'],
            'genres' => ['nullable', 'array'],
        ]);

        $comic->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'cover_image' => $request->input('cover_image', $comic->cover_image),
            'author_name' => $request->input('author_name'),
            'artist_name' => $request->input('artist_name'),
            'status' => $request->input('status'),
        ]);

        if ($request->has('genres')) {
            $comic->genres()->sync($request->input('genres'));
        }

        return redirect()->route('publisher.dashboard')->with('success', "Komik {$comic->title} berhasil diperbarui.");
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        $user = $request->user();
        $comic = Comic::where('id', $id)
            ->where('publisher_id', $user->id)
            ->firstOrFail();

        $comic->delete();

        return redirect()->route('publisher.dashboard')->with('success', "Komik {$comic->title} telah dihapus.");
    }
}

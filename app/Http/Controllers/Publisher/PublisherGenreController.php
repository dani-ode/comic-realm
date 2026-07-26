<?php

namespace App\Http\Controllers\Publisher;

use App\Domain\Comic\Models\Genre;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublisherGenreController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50', 'unique:genres,name'],
        ], [
            'name.unique' => 'Genre dengan nama ini sudah ada.',
            'name.min'    => 'Nama genre minimal 2 karakter.',
            'name.max'    => 'Nama genre maksimal 50 karakter.',
        ]);

        $genre = Genre::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'genre'   => [
                'id'   => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
            ],
        ]);
    }
}

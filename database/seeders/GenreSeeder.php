<?php

namespace Database\Seeders;

use App\Domain\Comic\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            ['name' => 'Action', 'slug' => 'action', 'description' => 'High-stakes fighting, martial arts, and intense action stories.'],
            ['name' => 'Adventure', 'slug' => 'adventure', 'description' => 'Epic journeys, exploration, and questing across fantasy lands.'],
            ['name' => 'Comedy', 'slug' => 'comedy', 'description' => 'Hilarious situations, funny characters, and lighthearted humor.'],
            ['name' => 'Drama', 'slug' => 'drama', 'description' => 'Emotional storylines, character growth, and deep plotlines.'],
            ['name' => 'Fantasy', 'slug' => 'fantasy', 'description' => 'Magical realms, mythical creatures, and heroic quests.'],
            ['name' => 'Horror', 'slug' => 'horror', 'description' => 'Supernatural thrillers, scary monsters, and spooky tales.'],
            ['name' => 'Romance', 'slug' => 'romance', 'description' => 'Heartwarming love stories, relationship dynamics, and sweet moments.'],
            ['name' => 'Sci-Fi', 'slug' => 'sci-fi', 'description' => 'Futuristic technology, space travel, and cybernetic worlds.'],
            ['name' => 'Slice of Life', 'slug' => 'slice-of-life', 'description' => 'Everyday experiences, school life, and realistic stories.'],
            ['name' => 'Thriller', 'slug' => 'thriller', 'description' => 'Suspenseful mysteries, psychological puzzles, and mind games.'],
        ];

        foreach ($genres as $genre) {
            Genre::firstOrCreate(['slug' => $genre['slug']], $genre);
        }
    }
}

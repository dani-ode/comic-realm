<?php

namespace Database\Seeders;

use App\Domain\Comic\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Genre adalah master data statis — hardcode nama & slug adalah benar
     * karena genre merupakan konfigurasi sistem, bukan data user/bisnis.
     */
    public function run(): void
    {
        $genres = [
            [
                'name'        => 'Action',
                'slug'        => 'action',
                'description' => 'Pertarungan seru, bela diri, dan cerita penuh aksi intens.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Adventure',
                'slug'        => 'adventure',
                'description' => 'Perjalanan epik, eksplorasi, dan pencarian di dunia fantasi.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Comedy',
                'slug'        => 'comedy',
                'description' => 'Situasi lucu, karakter kocak, dan humor yang menghibur.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Drama',
                'slug'        => 'drama',
                'description' => 'Alur emosional, pertumbuhan karakter, dan konflik mendalam.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Fantasy',
                'slug'        => 'fantasy',
                'description' => 'Dunia sihir, makhluk mitologis, dan pahlawan legendaris.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Horror',
                'slug'        => 'horror',
                'description' => 'Thriller supernatural, monster menyeramkan, dan kisah horor.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Romance',
                'slug'        => 'romance',
                'description' => 'Kisah cinta yang menghangatkan hati dan dinamika hubungan.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Sci-Fi',
                'slug'        => 'sci-fi',
                'description' => 'Teknologi masa depan, perjalanan antariksa, dan dunia siber.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Slice of Life',
                'slug'        => 'slice-of-life',
                'description' => 'Kehidupan sehari-hari, cerita sekolah, dan kisah realistis.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Thriller',
                'slug'        => 'thriller',
                'description' => 'Misteri mencekam, teka-teki psikologis, dan ketegangan pikiran.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Isekai',
                'slug'        => 'isekai',
                'description' => 'Karakter yang berpindah ke dunia lain atau reinkarnasi di dunia baru.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Sports',
                'slug'        => 'sports',
                'description' => 'Semangat kompetisi, latihan keras, dan perjuangan atlet muda.',
                'is_active'   => true,
            ],
        ];

        foreach ($genres as $genre) {
            Genre::firstOrCreate(
                ['slug' => $genre['slug']],
                $genre
            );
        }
    }
}

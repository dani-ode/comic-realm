<?php

namespace Database\Seeders;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Engagement\Models\Bookmark;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Rating;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class InteractionSeeder extends Seeder
{
    /**
     * InteractionSeeder membuat interaksi user yang realistis.
     *
     * Hardcode yang diperbolehkan:
     * - Teks komentar (konten demo, bukan data bisnis)
     * - Nilai rating (range 3–5 untuk mencerminkan distribusi yang masuk akal)
     *
     * Tidak hardcode:
     * - User → diambil dari database
     * - Comic & Chapter → diambil dari database
     * - Rating per user berbeda-beda (bukan semua 5 bintang)
     */
    public function run(): void
    {
        $comics = Comic::with('chapters')->get();

        if ($comics->isEmpty()) {
            $this->command->warn('Tidak ada komik ditemukan. Pastikan ComicSeeder dijalankan lebih dulu.');
            return;
        }

        // Ambil semua reader (role = user)
        $readers = User::where('role', 'user')->get();

        if ($readers->isEmpty()) {
            $this->command->warn('Tidak ada reader ditemukan.');
            return;
        }

        // ── Komentar realistis berdasarkan tipe komentar ──────────────────────
        // Setiap komentar berbeda per user agar tidak monoton
        $reviewTexts = [
            'Alur ceritanya sangat menarik, tidak bisa berhenti baca!',
            'Artwork-nya memukau, setiap panel terasa hidup.',
            'Karakter utamanya punya depth yang luar biasa.',
            'Plot twist di chapter ini benar-benar tidak terduga!',
            'Paling suka bagian aksinya, sangat dinamis dan seru.',
            'Ceritanya mengalir dengan baik, tidak ada bagian yang membosankan.',
            'Visual dan storytelling-nya pas banget, recommended!',
            'Ending chapter-nya selalu bikin penasaran untuk baca lanjutannya.',
            'Salah satu webtoon terbaik yang pernah saya baca tahun ini.',
            'World-building-nya solid, detail sekali.',
        ];

        $commentTexts = [
            'Ini chapter paling seru sejauh ini!',
            'Tidak sabar menunggu chapter berikutnya.',
            'Karakter ini jadi favorit saya dari awal.',
            'Bagian ini emosional banget, sampai mewek sedikit.',
            'Plot twist ini benar-benar mengejutkan!',
            'Gambarnya makin bagus dari chapter ke chapter.',
            'Dialognya natural dan witty sekali.',
            'Scene fight-nya keren abis, terasa epic.',
            'Chemistry antar karakter makin terasa kuat.',
            'Lanjut terus! Saya selalu menunggu update-nya.',
        ];

        $i = 0; // counter untuk rotasi teks

        foreach ($comics as $comic) {
            $firstChapter = $comic->chapters->sortBy('chapter_number')->first();

            foreach ($readers as $idx => $reader) {
                // ── Bookmark: tidak semua reader bookmark semua komik ──────────
                // Setiap reader bookmark sekitar 60% komik (berdasarkan parity)
                $shouldBookmark = ($comic->id + $reader->id) % 5 !== 0;
                if ($shouldBookmark) {
                    Bookmark::firstOrCreate([
                        'user_id'  => $reader->id,
                        'comic_id' => $comic->id,
                    ], [
                        'notify_updates' => (bool)(($comic->id + $reader->id) % 3 !== 0),
                    ]);
                }

                // ── Rating: distribusi bervariasi (3–5 bintang) ───────────────
                // Rating lebih tinggi untuk reader pertama (early adopter / fans)
                $ratingMap = [4, 5, 5, 4, 3]; // pola berbeda per reader index
                $ratingValue = $ratingMap[$idx % count($ratingMap)];

                Rating::updateOrCreate(
                    ['user_id' => $reader->id, 'comic_id' => $comic->id],
                    [
                        'rating'      => $ratingValue,
                        'review_text' => $reviewTexts[($i + $idx) % count($reviewTexts)],
                    ]
                );

                // ── Komentar di komik (overview/general) ──────────────────────
                Comment::firstOrCreate(
                    [
                        'user_id'    => $reader->id,
                        'comic_id'   => $comic->id,
                        'chapter_id' => null, // komentar di halaman komik, bukan chapter
                        'parent_id'  => null,
                    ],
                    [
                        'comment_text' => $commentTexts[($i + $idx) % count($commentTexts)],
                        'is_spoiler'   => false,
                        'status'       => 'published',
                        'likes_count'  => rand(0, 50),
                    ]
                );

                // ── Komentar di chapter pertama ────────────────────────────────
                if ($firstChapter) {
                    Comment::firstOrCreate(
                        [
                            'user_id'    => $reader->id,
                            'comic_id'   => $comic->id,
                            'chapter_id' => $firstChapter->id,
                            'parent_id'  => null,
                        ],
                        [
                            'comment_text' => $commentTexts[($i + $idx + 3) % count($commentTexts)],
                            'is_spoiler'   => (($i + $idx) % 7 === 0), // sesekali spoiler
                            'status'       => (($i + $idx) % 11 === 0) ? 'flagged' : 'published',
                            'likes_count'  => rand(0, 30),
                        ]
                    );
                }
            }

            $i++;
        }

        // ── Update aggregat di tabel comics ──────────────────────────────────
        // Supaya total_bookmarks, total_ratings, rating_average di tabel comics akurat
        foreach ($comics as $comic) {
            $avgRating = Rating::where('comic_id', $comic->id)->avg('rating') ?? $comic->rating_average;
            $totalRatings = Rating::where('comic_id', $comic->id)->count();
            $totalBookmarks = Bookmark::where('comic_id', $comic->id)->count();

            $comic->update([
                'total_ratings'   => $totalRatings,
                'rating_average'  => round($avgRating, 2),
                'total_bookmarks' => $totalBookmarks,
            ]);
        }
    }
}

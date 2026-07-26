<?php

namespace Database\Seeders;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\ChapterPage;
use App\Domain\Comic\Models\Comic;
use App\Domain\Comic\Models\Genre;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComicSeeder extends Seeder
{
    /**
     * ComicSeeder membuat komik sample yang realistis.
     *
     * Hardcode yang diperbolehkan:
     * - Judul, slug, deskripsi komik (konten cerita fiksi, bukan data user/bisnis)
     * - Nomor chapter & status chapter (bagian dari konfigurasi demo)
     * - Harga chapter (nilai bisnis yang sudah ditentukan)
     *
     * Tidak hardcode:
     * - author_name / artist_name → diambil dari data publisher yang sudah di-seed
     * - publisher_id → diambil dari user yang sudah di-seed
     * - Genre → dipilih secara relevan berdasarkan tipe komik
     * - cover_image → pakai URL Picsum deterministik (bukan URL random)
     */
    public function run(): void
    {
        // Ambil publisher dari database (sudah di-seed oleh PublisherSeeder)
        $daniUser  = User::where('email', 'publisher@comicrealm.test')->first();
        $realmUser = User::where('email', 'realm@comicrealm.test')->first();

        if (! $daniUser || ! $realmUser) {
            $this->command->warn('Publisher users not found. Pastikan PublisherSeeder dijalankan lebih dulu.');
            return;
        }

        $daniBrand  = $daniUser->publisherProfile?->brand_name ?? $daniUser->name;
        $realmBrand = $realmUser->publisherProfile?->brand_name ?? $realmUser->name;

        // Ambil genre dari database (sudah di-seed oleh GenreSeeder)
        $genres = Genre::where('is_active', true)->get()->keyBy('slug');

        // ── Data Komik ─────────────────────────────────────────────────────────
        // cover_image pakai Picsum dengan seed deterministik agar konsisten
        $comics = [
            // ── Dani Comic Studio ──────────────────────────────────────────────
            [
                'publisher_id'       => $daniUser->id,
                'author_name'        => $daniUser->name,
                'artist_name'        => $daniBrand,
                'title'              => 'Solo Hunter: The Awakening',
                'slug'               => 'solo-hunter-the-awakening',
                'description'        => "Di dunia yang dilanda invasi dungeon dimensi, seorang hunter peringkat terendah secara tak sengaja memperoleh sistem leveling tersembunyi. Kini ia harus membuktikan dirinya di medan yang penuh bahaya sendirian.",
                'cover_image'        => 'https://picsum.photos/seed/solo-hunter/400/600',
                'banner_image'       => 'https://picsum.photos/seed/solo-hunter-banner/1200/400',
                'status'             => 'ongoing',
                'publication_status' => 'published',
                'content_rating'     => 'teen',
                'is_featured'        => true,
                'total_views'        => 125_000,
                'rating_average'     => 4.95,
                'published_at'       => now()->subMonths(7),
                'genre_slugs'        => ['action', 'fantasy', 'adventure'],
                'chapters'           => [
                    ['number' => 1, 'title' => 'Kebangkitan',          'is_free' => true,  'price' => 0,     'views' => 45_000],
                    ['number' => 2, 'title' => 'Sistem Tersembunyi',   'is_free' => true,  'price' => 0,     'views' => 38_000],
                    ['number' => 3, 'title' => 'Dungeon Pertama',      'is_free' => false, 'price' => 5_000, 'views' => 22_000],
                    ['number' => 4, 'title' => 'Level Up',             'is_free' => false, 'price' => 5_000, 'views' => 18_000],
                    ['number' => 5, 'title' => 'Musuh Sejati',         'is_free' => false, 'price' => 5_000, 'views' => 12_000],
                    ['number' => 6, 'title' => 'Batas Kekuatan',       'is_free' => false, 'price' => 5_000, 'views' => 8_500],
                    ['number' => 7, 'title' => 'Pertarungan Puncak',   'is_free' => false, 'price' => 7_000, 'views' => 6_000],
                ],
            ],
            [
                'publisher_id'       => $daniUser->id,
                'author_name'        => $daniUser->name,
                'artist_name'        => $daniBrand,
                'title'              => 'Auto Hunting With My Clones',
                'slug'               => 'auto-hunting-with-my-clones',
                'description'        => "Bayangkan kloningmu bisa otomatis farming dungeon saat kamu tidur. Park Jin, pemuda biasa dengan power tidak terpakai, menemukan kemampuan unik yang tak ada duanya. Kekuatan tak terbatas dimulai dari sini.",
                'cover_image'        => 'https://picsum.photos/seed/auto-hunting/400/600',
                'banner_image'       => 'https://picsum.photos/seed/auto-hunting-banner/1200/400',
                'status'             => 'ongoing',
                'publication_status' => 'published',
                'content_rating'     => 'teen',
                'is_featured'        => true,
                'total_views'        => 98_000,
                'rating_average'     => 4.88,
                'published_at'       => now()->subMonths(5),
                'genre_slugs'        => ['action', 'fantasy', 'comedy'],
                'chapters'           => [
                    ['number' => 1, 'title' => 'Kloning Pertama',      'is_free' => true,  'price' => 0,     'views' => 35_000],
                    ['number' => 2, 'title' => 'Farming Mode',         'is_free' => true,  'price' => 0,     'views' => 28_000],
                    ['number' => 3, 'title' => 'Level Ganda',          'is_free' => false, 'price' => 5_000, 'views' => 20_000],
                    ['number' => 4, 'title' => 'Kloning Berbahaya',    'is_free' => false, 'price' => 5_000, 'views' => 15_000],
                    ['number' => 5, 'title' => 'Identitas Ganda',      'is_free' => false, 'price' => 5_000, 'views' => 10_000],
                ],
            ],
            [
                'publisher_id'       => $daniUser->id,
                'author_name'        => $daniUser->name,
                'artist_name'        => $daniBrand,
                'title'              => 'Sang Penjaga Terakhir',
                'slug'               => 'sang-penjaga-terakhir',
                'description'        => "Seorang master pedang kuno terbangun 1000 tahun ke masa depan dan mendapati kerajaannya telah hancur. Dengan kekuatan yang masih membara, ia memulai perjalanan untuk memulihkan kejayaan yang hilang.",
                'cover_image'        => 'https://picsum.photos/seed/sang-penjaga/400/600',
                'banner_image'       => 'https://picsum.photos/seed/sang-penjaga-banner/1200/400',
                'status'             => 'hiatus',
                'publication_status' => 'published',
                'content_rating'     => 'teen',
                'is_featured'        => false,
                'total_views'        => 31_000,
                'rating_average'     => 4.60,
                'published_at'       => now()->subMonths(9),
                'genre_slugs'        => ['fantasy', 'action', 'adventure'],
                'chapters'           => [
                    ['number' => 1, 'title' => 'Kebangkitan Sang Penjaga', 'is_free' => true,  'price' => 0,     'views' => 18_000],
                    ['number' => 2, 'title' => 'Dunia yang Berubah',       'is_free' => true,  'price' => 0,     'views' => 12_000],
                    ['number' => 3, 'title' => 'Musuh Abadi',              'is_free' => false, 'price' => 4_000, 'views' => 7_000],
                ],
            ],

            // ── Realm Art Studio ───────────────────────────────────────────────
            [
                'publisher_id'       => $realmUser->id,
                'author_name'        => $realmUser->name,
                'artist_name'        => $realmBrand,
                'title'              => 'Bintang di Antara Kita',
                'slug'               => 'bintang-di-antara-kita',
                'description'        => "Dua mahasiswa seni yang awalnya bertolak belakang karakter justru berkolaborasi dalam proyek pameran bergengsi. Di balik perbedaan mereka, perlahan-lahan tumbuh perasaan yang tak terduga.",
                'cover_image'        => 'https://picsum.photos/seed/bintang-kita/400/600',
                'banner_image'       => 'https://picsum.photos/seed/bintang-kita-banner/1200/400',
                'status'             => 'ongoing',
                'publication_status' => 'published',
                'content_rating'     => 'all_ages',
                'is_featured'        => true,
                'total_views'        => 72_000,
                'rating_average'     => 4.82,
                'published_at'       => now()->subMonths(4),
                'genre_slugs'        => ['romance', 'slice-of-life', 'drama'],
                'chapters'           => [
                    ['number' => 1, 'title' => 'Pertemuan Tak Terduga', 'is_free' => true,  'price' => 0,     'views' => 28_000],
                    ['number' => 2, 'title' => 'Proyek Bersama',        'is_free' => true,  'price' => 0,     'views' => 22_000],
                    ['number' => 3, 'title' => 'Salah Paham',           'is_free' => false, 'price' => 4_000, 'views' => 15_000],
                    ['number' => 4, 'title' => 'Malam Pameran',         'is_free' => false, 'price' => 4_000, 'views' => 10_000],
                    ['number' => 5, 'title' => 'Perasaan yang Jujur',   'is_free' => false, 'price' => 4_000, 'views' => 6_500],
                ],
            ],
            [
                'publisher_id'       => $realmUser->id,
                'author_name'        => $realmUser->name,
                'artist_name'        => $realmBrand,
                'title'              => 'Kode 404: Dunia Lain',
                'slug'               => 'kode-404-dunia-lain',
                'description'        => "Seorang programmer jenius tersedot ke dalam game RPG yang ia ciptakan sendiri. Dengan pengetahuan kode program di dunia nyata, ia mencoba mengeksploitasi bug sistem untuk selamat dan kembali ke dunia aslinya.",
                'cover_image'        => 'https://picsum.photos/seed/kode-404/400/600',
                'banner_image'       => 'https://picsum.photos/seed/kode-404-banner/1200/400',
                'status'             => 'ongoing',
                'publication_status' => 'published',
                'content_rating'     => 'teen',
                'is_featured'        => false,
                'total_views'        => 45_000,
                'rating_average'     => 4.70,
                'published_at'       => now()->subMonths(3),
                'genre_slugs'        => ['sci-fi', 'action', 'isekai'],
                'chapters'           => [
                    ['number' => 1, 'title' => 'Masuk ke Dalam Game', 'is_free' => true,  'price' => 0,     'views' => 18_000],
                    ['number' => 2, 'title' => 'Debug Mode',          'is_free' => true,  'price' => 0,     'views' => 14_000],
                    ['number' => 3, 'title' => 'Exploit Pertama',     'is_free' => false, 'price' => 5_000, 'views' => 9_000],
                    ['number' => 4, 'title' => 'Game Master',         'is_free' => false, 'price' => 5_000, 'views' => 5_000],
                ],
            ],
        ];

        // ── Simpan ke database ─────────────────────────────────────────────────
        foreach ($comics as $data) {
            $genreSlugs = $data['genre_slugs'] ?? [];
            $chaptersData = $data['chapters'] ?? [];

            unset($data['genre_slugs'], $data['chapters']);

            // updateOrCreate agar seeder bisa dijalankan berulang kali (idempotent)
            $comic = Comic::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['language' => 'id'])
            );

            // Attach genre yang relevan
            $genreIds = collect($genreSlugs)
                ->map(fn($slug) => $genres->get($slug)?->id)
                ->filter()
                ->values()
                ->toArray();

            if (! empty($genreIds)) {
                $comic->genres()->sync($genreIds);
            }

            // ── Buat chapter-chapter ──────────────────────────────────────────
            foreach ($chaptersData as $ch) {
                // slug unik per komik: {comic-slug}-ch-{number}
                $chSlug = $comic->slug . '-ch-' . str_replace('.', '-', $ch['number']);

                // published_at mundur dari sekarang secara terurut
                $publishedAt = now()->subMonths(
                    count($chaptersData) - array_search($ch, $chaptersData)
                );

                $chapter = Chapter::firstOrCreate(
                    [
                        'comic_id'       => $comic->id,
                        'chapter_number' => $ch['number'],
                    ],
                    [
                        'title'        => $ch['title'],
                        'slug'         => $chSlug,
                        'is_free'      => $ch['is_free'],
                        'price'        => $ch['price'],
                        'currency'     => 'IDR',
                        'status'       => 'published',
                        'total_views'  => $ch['views'],
                        'published_at' => $publishedAt,
                    ]
                );

                // ── Buat sample pages ────────────────────────────────────────
                // Pakai seed deterministik berdasarkan comic+chapter agar konsisten
                $baseSeed = crc32($comic->slug . '-ch' . $ch['number']);
                $pageCount = 12; // jumlah halaman realistis per chapter

                for ($p = 1; $p <= $pageCount; $p++) {
                    // ID Picsum deterministik (1–1000), variatif per halaman
                    $picId = (abs($baseSeed) % 900) + ($p * 7) % 100 + 10;

                    ChapterPage::firstOrCreate(
                        [
                            'chapter_id'  => $chapter->id,
                            'page_number' => $p,
                        ],
                        [
                            'image_path' => "comics/{$comic->slug}/ch{$ch['number']}/page-{$p}.webp",
                            'image_url'  => "https://picsum.photos/id/{$picId}/800/1200",
                            'width'      => 800,
                            'height'     => 1200,
                            'file_size'  => rand(90_000, 180_000),
                            'mime_type'  => 'image/webp',
                        ]
                    );
                }
            }
        }
    }
}

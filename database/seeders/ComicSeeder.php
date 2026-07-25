<?php

namespace Database\Seeders;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\ChapterPage;
use App\Domain\Comic\Models\Comic;
use App\Domain\Comic\Models\Genre;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class ComicSeeder extends Seeder
{
    public function run(): void
    {
        $publisher = User::firstOrCreate(
            ['email' => 'publisher@comicrealm.test'],
            [
                'name' => 'Dani Studio',
                'username' => 'danistudio',
                'password' => bcrypt('password123'),
                'role' => 'publisher',
                'status' => 'active',
            ]
        );
        $publisher->profile()->firstOrCreate([]);

        $genres = Genre::all();

        $pexelsImage = 'https://images.pexels.com/photos/27638736/pexels-photo-27638736.jpeg?auto=compress&cs=tinysrgb&w=800';

        $comicsData = [
            [
                'title' => 'Solo Hunter: The Awakening',
                'slug' => 'solo-hunter-the-awakening',
                'description' => 'In a world invaded by dimensional dungeons, a lowest-rank hunter accidentally obtains a hidden leveling system.',
                'cover_image' => $pexelsImage,
                'banner_image' => $pexelsImage,
                'author_name' => 'Mahdani',
                'artist_name' => 'Realm Art',
                'is_featured' => true,
                'rating_average' => 4.95,
                'total_views' => 125000,
            ],
            [
                'title' => 'Auto Hunting With My Clones',
                'slug' => 'auto-hunting-with-my-clones',
                'description' => 'What happens when your shadow clones can auto-farm dungeons while you sleep? Unlimited power!',
                'cover_image' => 'https://picsum.photos/id/1062/400/600',
                'banner_image' => 'https://picsum.photos/id/1062/1200/400',
                'author_name' => 'Zero',
                'artist_name' => 'Clone Studio',
                'is_featured' => true,
                'rating_average' => 4.88,
                'total_views' => 98000,
            ],
            [
                'title' => 'The Last Guardian of Eldoria',
                'slug' => 'the-last-guardian-of-eldoria',
                'description' => 'An ancient spell sword master reawakens 1000 years into the future to find his empire in ruins.',
                'cover_image' => 'https://picsum.photos/id/1074/400/600',
                'banner_image' => 'https://picsum.photos/id/1074/1200/400',
                'author_name' => 'Eldor',
                'artist_name' => 'Fantasy Works',
                'is_featured' => false,
                'rating_average' => 4.75,
                'total_views' => 45000,
            ],
        ];

        foreach ($comicsData as $data) {
            $comic = Comic::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'publisher_id' => $publisher->id,
                    'status' => 'ongoing',
                    'publication_status' => 'published',
                    'content_rating' => 'all_ages',
                    'published_at' => now(),
                ])
            );

            // Attach random genres
            if ($genres->isNotEmpty()) {
                $comic->genres()->sync($genres->random(min(3, $genres->count()))->pluck('id'));
            }

            // Create sample chapters (Chapter 1 & 2 Free, Chapter 3 Paid)
            for ($i = 1; $i <= 5; $i++) {
                $isFree = $i <= 2;
                $chapter = Chapter::firstOrCreate(
                    ['comic_id' => $comic->id, 'chapter_number' => $i],
                    [
                        'title' => "Chapter {$i}: " . ($i === 1 ? 'The Beginning' : "Step {$i}"),
                        'slug' => "chapter-{$i}",
                        'is_free' => $isFree,
                        'price' => $isFree ? 0 : 5000,
                        'currency' => 'IDR',
                        'status' => 'published',
                        'total_views' => rand(500, 5000),
                        'published_at' => now(),
                    ]
                );

                // Sample pages for each chapter
                for ($p = 1; $p <= 10; $p++) {
                    ChapterPage::firstOrCreate(
                        ['chapter_id' => $chapter->id, 'page_number' => $p],
                        [
                            'image_path' => "comics/{$comic->slug}/ch{$i}/{$p}.webp",
                            'image_url' => "https://picsum.photos/id/" . (100 + $p * 3) . "/800/1200",
                            'width' => 800,
                            'height' => 1200,
                            'file_size' => 120000,
                            'mime_type' => 'image/webp',
                        ]
                    );
                }
            }
        }
    }
}

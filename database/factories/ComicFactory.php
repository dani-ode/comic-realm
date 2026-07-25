<?php

namespace Database\Factories;

use App\Domain\Comic\Enums\ComicPublicationStatus;
use App\Domain\Comic\Enums\ComicStatus;
use App\Domain\Comic\Enums\ContentRating;
use App\Domain\Comic\Models\Comic;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComicFactory extends Factory
{
    protected $model = Comic::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'publisher_id' => User::factory()->publisher(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999),
            'alternative_title' => fake()->optional()->sentence(2),
            'description' => fake()->paragraph(3),
            'cover_image' => 'https://picsum.photos/400/600',
            'banner_image' => 'https://picsum.photos/1200/400',
            'author_name' => fake()->name(),
            'artist_name' => fake()->name(),
            'status' => ComicStatus::ONGOING,
            'publication_status' => ComicPublicationStatus::PUBLISHED,
            'content_rating' => ContentRating::ALL_AGES,
            'language' => 'id',
            'total_views' => fake()->numberBetween(100, 50000),
            'total_bookmarks' => fake()->numberBetween(10, 5000),
            'total_ratings' => fake()->numberBetween(5, 500),
            'rating_average' => fake()->randomFloat(2, 4, 5),
            'is_featured' => fake()->boolean(20),
            'published_at' => now(),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'featured_at' => now(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Domain\Comic\Enums\ChapterStatus;
use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    public function definition(): array
    {
        $number = fake()->numberBetween(1, 50);
        $title = "Chapter {$number}";
        return [
            'comic_id' => Comic::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'chapter_number' => $number,
            'description' => fake()->optional()->sentence(),
            'is_free' => true,
            'price' => 0,
            'currency' => 'IDR',
            'status' => ChapterStatus::PUBLISHED,
            'total_views' => fake()->numberBetween(50, 10000),
            'published_at' => now(),
        ];
    }

    public function paid(int $price = 5000): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free' => false,
            'price' => $price,
        ]);
    }
}

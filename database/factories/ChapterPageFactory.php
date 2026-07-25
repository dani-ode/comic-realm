<?php

namespace Database\Factories;

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\ChapterPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChapterPageFactory extends Factory
{
    protected $model = ChapterPage::class;

    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'page_number' => 1,
            'image_path' => 'comics/sample/001.webp',
            'image_url' => 'https://picsum.photos/800/1200',
            'width' => 800,
            'height' => 1200,
            'file_size' => 150000,
            'mime_type' => 'image/webp',
        ];
    }
}

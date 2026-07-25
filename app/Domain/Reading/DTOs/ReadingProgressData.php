<?php

namespace App\Domain\Reading\DTOs;

use Spatie\LaravelData\Data;

class ReadingProgressData extends Data
{
    public function __construct(
        public int $comic_id,
        public int $chapter_id,
        public int $page_number,
        public float $progress_percent,
    ) {}
}

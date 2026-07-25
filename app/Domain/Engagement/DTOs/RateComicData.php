<?php

namespace App\Domain\Engagement\DTOs;

use Spatie\LaravelData\Data;

class RateComicData extends Data
{
    public function __construct(
        public int $comic_id,
        public int $rating,
        public ?string $review_text = null,
    ) {}
}

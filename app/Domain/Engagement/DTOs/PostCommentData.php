<?php

namespace App\Domain\Engagement\DTOs;

use Spatie\LaravelData\Data;

class PostCommentData extends Data
{
    public function __construct(
        public int $comic_id,
        public string $comment_text,
        public ?int $chapter_id = null,
        public ?int $parent_id = null,
        public bool $is_spoiler = false,
    ) {}
}

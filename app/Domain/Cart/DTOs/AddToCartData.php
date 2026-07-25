<?php

namespace App\Domain\Cart\DTOs;

use Spatie\LaravelData\Data;

class AddToCartData extends Data
{
    public function __construct(
        public int $chapter_id,
    ) {}
}

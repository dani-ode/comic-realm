<?php

namespace App\Domain\Publisher\DTOs;

use Spatie\LaravelData\Data;

class ApplyPublisherData extends Data
{
    public function __construct(
        public string $brand_name,
        public ?string $bio = null,
        public ?string $bank_name = null,
        public ?string $bank_account_number = null,
        public ?string $bank_account_name = null,
    ) {}
}

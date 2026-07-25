<?php

namespace App\Domain\User\DTOs;

use Spatie\LaravelData\Data;

class RegisterUserData extends Data
{
    public function __construct(
        public string $name,
        public string $username,
        public string $email,
        public string $password,
        public ?string $phone = null,
    ) {}
}

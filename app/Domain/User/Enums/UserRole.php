<?php

namespace App\Domain\User\Enums;

enum UserRole: string
{
    case USER = 'user';
    case PUBLISHER = 'publisher';
    case ADMIN = 'admin';
}

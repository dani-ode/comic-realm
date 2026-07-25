<?php

namespace App\Domain\Comic\Enums;

enum ComicStatus: string
{
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case HIATUS = 'hiatus';
    case CANCELLED = 'cancelled';
}

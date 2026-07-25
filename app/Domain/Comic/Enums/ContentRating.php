<?php

namespace App\Domain\Comic\Enums;

enum ContentRating: string
{
    case ALL_AGES = 'all_ages';
    case TEEN = 'teen';
    case MATURE = 'mature';
}

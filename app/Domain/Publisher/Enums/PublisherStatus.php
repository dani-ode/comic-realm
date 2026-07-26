<?php

namespace App\Domain\Publisher\Enums;

enum PublisherStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case BLOCKED = 'blocked';
}

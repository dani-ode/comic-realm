<?php

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'UNPAID';
    case PAID = 'PAID';
    case EXPIRED = 'EXPIRED';
    case FAILED = 'FAILED';
    case REFUND = 'REFUND';
}

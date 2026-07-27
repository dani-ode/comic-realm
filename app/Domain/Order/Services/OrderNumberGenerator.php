<?php

namespace App\Domain\Order\Services;

use App\Domain\Order\Models\Order;
use Illuminate\Support\Str;

class OrderNumberGenerator
{
    public function generate(): string
    {
        $datePrefix = now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        $orderNumber = "INV-{$datePrefix}-{$random}";

        while (Order::where('order_number', $orderNumber)->exists()) {
            $random = strtoupper(Str::random(6));
            $orderNumber = "INV-{$datePrefix}-{$random}";
        }

        return $orderNumber;
    }
}

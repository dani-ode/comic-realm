<?php

namespace App\Domain\Payment\Contracts;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function getPaymentChannels(int $amount = 0): array;

    public function calculateFee(int $amount, ?string $channelCode = null): array;

    public function createClosedTransaction(Order $order, string $paymentMethod): Payment;

    public function verifyWebhook(Request $request): array;

    public function checkTransactionStatus(string $reference): array;
}

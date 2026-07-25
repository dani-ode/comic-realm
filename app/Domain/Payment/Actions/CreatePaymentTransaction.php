<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;

class CreatePaymentTransaction
{
    public function __construct(
        protected PaymentGateway $gateway
    ) {}

    public function execute(Order $order, string $paymentMethod): Payment
    {
        return DB::transaction(function () use ($order, $paymentMethod) {
            // Periksa jika sudah ada pembayaran UNPAID untuk order ini
            $existing = Payment::where('order_id', $order->id)
                ->where('status', 'UNPAID')
                ->where('payment_method', strtoupper($paymentMethod))
                ->first();

            if ($existing) {
                return $existing;
            }

            return $this->gateway->createClosedTransaction($order, $paymentMethod);
        });
    }
}

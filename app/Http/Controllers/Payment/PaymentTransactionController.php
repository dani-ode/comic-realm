<?php

namespace App\Http\Controllers\Payment;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Actions\CreatePaymentTransaction;
use App\Domain\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use RuntimeException;

class PaymentTransactionController extends Controller
{
    public function store(Request $request, CreatePaymentTransaction $createPayment): JsonResponse
    {
        $request->validate([
            'order_number' => ['required', 'string', 'exists:orders,order_number'],
            'payment_method' => ['required', 'string'],
        ]);

        $order = Order::with(['items.comic', 'user'])
            ->where('user_id', $request->user()->id)
            ->where('order_number', $request->input('order_number'))
            ->firstOrFail();

        try {
            $payment = $createPayment->execute($order, $request->input('payment_method'));

            return response()->json([
                'success' => true,
                'payment' => $payment,
                'redirect_url' => route('payment.show', $payment->tripay_reference),
                'message' => 'Transaksi pembayaran berhasil dibuat.',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(string $reference, Request $request): InertiaResponse
    {
        $payment = Payment::with(['order.items.comic'])
            ->where('user_id', $request->user()->id)
            ->where('tripay_reference', $reference)
            ->firstOrFail();

        return Inertia::render('Payment/Show', [
            'payment' => $payment,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Payment;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Actions\CreatePaymentTransaction;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        $payment = Payment::with(['order.items.comic', 'user'])
            ->where('user_id', $request->user()->id)
            ->where('tripay_reference', $reference)
            ->firstOrFail();

        return Inertia::render('Payment/Show', [
            'payment' => $payment,
        ]);
    }

    public function checkStatus(Request $request, PaymentGateway $gateway): JsonResponse
    {
        $request->validate([
            'reference' => ['required', 'string'],
        ]);

        $reference = $request->input('reference');
        Log::info("[User Action Request] POST : " . $request->fullUrl() . " (Ref: {$reference})");

        $payment = Payment::with(['order.items'])
            ->where('user_id', $request->user()->id)
            ->where('tripay_reference', $reference)
            ->firstOrFail();

        $triPayResult = $gateway->checkTransactionStatus($payment->tripay_reference);

        Log::info("PaymentTransactionController@checkStatus TriPay result for {$reference}:", $triPayResult);

        if (! ($triPayResult['success'] ?? false)) {
            Log::warning("checkStatus failed for {$reference}: " . ($triPayResult['message'] ?? 'Unknown error'));
            return response()->json([
                'success' => false,
                'message' => $triPayResult['message'] ?? 'Gagal terhubung ke server TriPay.',
                'status' => $payment->status instanceof PaymentStatus ? $payment->status->value : (string) $payment->status,
            ]);
        }

        $triPayStatus = strtoupper($triPayResult['status'] ?? 'UNPAID');
        $localStatus = $payment->status instanceof PaymentStatus ? $payment->status->value : (string) $payment->status;

        Log::info("checkStatus comparing status for {$reference}: TriPay Status = {$triPayStatus}, Local DB Status = {$localStatus}");

        if ($triPayStatus === 'PAID') {
            $payment->update([
                'status' => PaymentStatus::PAID,
                'paid_at' => now(),
            ]);

            if ($payment->order) {
                $payment->order->update([
                    'status' => OrderStatus::COMPLETED->value,
                    'completed_at' => now(),
                ]);

                if (Schema::hasTable('entitlements')) {
                    foreach ($payment->order->items as $item) {
                        DB::table('entitlements')->updateOrInsert(
                            [
                                'user_id' => $payment->order->user_id,
                                'chapter_id' => $item->chapter_id,
                            ],
                            [
                                'comic_id' => $item->comic_id,
                                'order_id' => $payment->order->id,
                                'granted_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }

                // Otomatis kredit royalti 70% ke dompet publisher
                app(\App\Domain\Wallet\Actions\CreditPublisherRoyalty::class)->execute($payment->order);
            }

            Log::info("checkStatus: Order {$payment->merchant_ref} successfully verified as PAID. Entitlement granted & Royalty credited.");

            return response()->json([
                'success' => true,
                'status' => 'PAID',
                'message' => 'Pembayaran dikonfirmasi LUNAS dari TriPay API!',
            ]);
        }

        if (in_array($triPayStatus, ['EXPIRED', 'FAILED', 'REFUND'])) {
            $payment->update(['status' => $triPayStatus]);
            if ($payment->order) {
                $targetOrderStatus = match ($triPayStatus) {
                    'EXPIRED' => OrderStatus::EXPIRED->value,
                    'FAILED' => OrderStatus::FAILED->value,
                    'REFUND' => OrderStatus::CANCELLED->value,
                    default => OrderStatus::CANCELLED->value,
                };
                $payment->order->update(['status' => $targetOrderStatus]);
            }
        }

        return response()->json([
            'success' => true,
            'status' => $triPayStatus,
            'message' => "Status transaksi saat ini: {$triPayStatus}",
        ]);
    }
    
}

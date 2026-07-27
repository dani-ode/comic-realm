<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProcessPaymentWebhook
{
    public function __construct(
        protected PaymentGateway $gateway
    ) {}

    public function execute(Request $request): array
    {
        Log::info('[TriPay Webhook Incoming Request] POST : ' . $request->fullUrl(), [
            'payload' => $request->all(),
            'headers' => [
                'x-callback-event' => $request->header('X-Callback-Event'),
                'x-callback-signature' => $request->header('X-Callback-Signature'),
            ],
        ]);

        $verification = $this->gateway->verifyWebhook($request);

        Log::info('TriPay Webhook Callback Received:', [
            'valid_signature' => $verification['valid'],
            'reference' => $verification['reference'] ?? null,
            'merchant_ref' => $verification['merchant_ref'] ?? null,
            'status' => $verification['status'] ?? null,
            'payload' => $request->all(),
        ]);

        if (! $verification['valid']) {
            Log::warning('TriPay Webhook signature mismatch', ['payload' => $request->all()]);
            return ['success' => false, 'message' => 'Invalid signature verification.'];
        }

        $merchantRef = $verification['merchant_ref'] ?? null;
        $status = strtoupper($verification['status'] ?? '');

        if (! $merchantRef) {
            return ['success' => false, 'message' => 'Merchant reference missing in callback.'];
        }

        return DB::transaction(function () use ($merchantRef, $status, $verification) {
            $payment = Payment::where('merchant_ref', $merchantRef)
                ->orWhere('tripay_reference', $verification['reference'] ?? '')
                ->first();

            if (! $payment) {
                Log::error('TriPay Webhook payment record not found', ['merchant_ref' => $merchantRef]);
                return ['success' => false, 'message' => 'Payment transaction record not found.'];
            }

            $order = Order::with('items')->find($payment->order_id);

            if ($status === 'PAID') {
                $isOrderCancelled = $order && in_array(strtolower($order->status instanceof OrderStatus ? $order->status->value : (string)$order->status), ['cancelled', 'expired']);
                $isPaymentCancelled = $payment->status === PaymentStatus::CANCELLED || $payment->status === PaymentStatus::EXPIRED;

                if ($isOrderCancelled || $isPaymentCancelled) {
                    $payment->update([
                        'status' => PaymentStatus::REFUND,
                        'paid_at' => now(),
                    ]);

                    Log::warning("TriPay Webhook: PAID received for CANCELLED order {$merchantRef}. Flagged as REFUND (Manual refund required).", [
                        'merchant_ref' => $merchantRef,
                        'amount' => $payment->amount,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Order was cancelled. Payment flagged for REFUND.',
                    ];
                }

                $payment->update([
                    'status' => PaymentStatus::PAID,
                    'paid_at' => now(),
                ]);

                if ($order && strtolower($order->status instanceof OrderStatus ? $order->status->value : (string)$order->status) !== 'completed') {
                    $order->update([
                        'status' => OrderStatus::COMPLETED->value,
                        'completed_at' => now(),
                    ]);

                    // Otomatis berikan Hak Baca (Entitlement) untuk setiap bab dalam pesanan
                    if (Schema::hasTable('entitlements')) {
                        foreach ($order->items as $item) {
                            DB::table('entitlements')->updateOrInsert(
                                [
                                    'user_id' => $order->user_id,
                                    'chapter_id' => $item->chapter_id,
                                ],
                                [
                                    'comic_id' => $item->comic_id,
                                    'order_id' => $order->id,
                                    'granted_at' => now(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }
                    }
                }

                Log::info("TriPay Webhook: Order {$merchantRef} successfully marked as PAID & Entitlement granted.");
                return ['success' => true, 'message' => 'Payment successfully completed.'];
            }

            if ($status === 'EXPIRED') {
                $payment->update(['status' => PaymentStatus::EXPIRED]);
                if ($order) {
                    $order->update(['status' => OrderStatus::EXPIRED->value]);
                }
                Log::info("TriPay Webhook: Order {$merchantRef} marked as EXPIRED.");
                return ['success' => true, 'message' => 'Payment status updated to EXPIRED.'];
            }

            if ($status === 'FAILED') {
                $payment->update(['status' => PaymentStatus::FAILED]);
                if ($order) {
                    $order->update(['status' => OrderStatus::FAILED->value]);
                }
                Log::info("TriPay Webhook: Order {$merchantRef} marked as FAILED.");
                return ['success' => true, 'message' => 'Payment status updated to FAILED.'];
            }

            if ($status === 'REFUND') {
                $payment->update(['status' => PaymentStatus::REFUND]);
                if ($order) {
                    $order->update(['status' => OrderStatus::CANCELLED->value]);
                }
                Log::info("TriPay Webhook: Order {$merchantRef} marked as REFUND.");
                return ['success' => true, 'message' => 'Payment status updated to REFUND.'];
            }

            if ($status === 'UNPAID') {
                $payment->update(['status' => PaymentStatus::UNPAID]);
                Log::info("TriPay Webhook: Order {$merchantRef} status is UNPAID.");
                return ['success' => true, 'message' => 'Payment status is UNPAID.'];
            }

            Log::info("TriPay Webhook: Unhandled status {$status} for order {$merchantRef}.");
            return ['success' => true, 'message' => 'Payment status processed: ' . $status];
        });
    }
}

<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Contracts\PaymentGateway;
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
        $verification = $this->gateway->verifyWebhook($request);

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

            if ($status === 'PAID') {
                $payment->update([
                    'status' => 'PAID',
                    'paid_at' => now(),
                ]);

                $order = Order::with('items')->find($payment->order_id);
                if ($order && $order->status !== 'completed') {
                    $order->update([
                        'status' => 'completed',
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

                Log::info("TriPay Webhook: Order {$merchantRef} successfully marked as PAID.");
                return ['success' => true, 'message' => 'Payment successfully completed.'];
            }

            if (in_array($status, ['EXPIRED', 'FAILED', 'REFUND'])) {
                $payment->update(['status' => $status]);
                $order = Order::find($payment->order_id);
                if ($order) {
                    $order->update(['status' => strtolower($status)]);
                }
            }

            return ['success' => true, 'message' => 'Payment status updated to ' . $status];
        });
    }
}

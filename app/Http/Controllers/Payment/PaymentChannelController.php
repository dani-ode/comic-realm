<?php

namespace App\Http\Controllers\Payment;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PaymentChannelController extends Controller
{
    public function __construct(
        protected PaymentGateway $gateway
    ) {}

    public function select(string $orderNumber, Request $request): InertiaResponse
    {
        $order = Order::with(['items.comic'])
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['pending', 'unpaid'])
            ->firstOrFail();

        $channels = $this->gateway->getPaymentChannels($order->total_amount);

        return Inertia::render('Payment/SelectChannel', [
            'order' => $order,
            'channels' => $channels,
        ]);
    }

    public function getChannels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->gateway->getPaymentChannels(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrderController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $orders = Order::with(['items.comic', 'payment'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Order/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(string $orderNumber, Request $request): InertiaResponse
    {
        $order = Order::with(['items.comic', 'payment'])
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return Inertia::render('Order/Show', [
            'order' => $order,
        ]);
    }

    public function cancel(string $orderNumber, Request $request): RedirectResponse
    {
        $order = Order::with('payment')
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $statusStr = $order->status instanceof OrderStatus ? $order->status->value : (string) $order->status;

        if (in_array(strtolower($statusStr), ['pending', 'unpaid'])) {
            $order->update(['status' => OrderStatus::CANCELLED->value]);
            if ($order->payment) {
                $order->payment->update(['status' => PaymentStatus::CANCELLED->value]);
            }

            return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
        }

        return redirect()->back()->with('error', 'Pesanan ini tidak dapat dibatalkan.');
    }
}

<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrderController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $orders = Order::with(['items.comic'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Order/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(string $orderNumber, Request $request): InertiaResponse
    {
        $order = Order::with(['items.comic', 'items.chapter'])
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return Inertia::render('Order/Show', [
            'order' => $order,
        ]);
    }
}

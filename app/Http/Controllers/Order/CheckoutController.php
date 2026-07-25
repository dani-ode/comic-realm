<?php

namespace App\Http\Controllers\Order;

use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function show(Request $request): InertiaResponse
    {
        $cart = Cart::with(['items.chapter.comic'])
            ->where('user_id', $request->user()->id)
            ->first();

        return Inertia::render('Order/Checkout', [
            'cart' => $cart,
        ]);
    }

    public function process(Request $request, CreateOrderFromCart $createOrder): JsonResponse
    {
        try {
            $order = $createOrder->execute($request->user());

            return response()->json([
                'success' => true,
                'order' => $order,
                'redirect_url' => route('orders.show', $order->order_number),
                'message' => 'Pesanan berhasil dibuat.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

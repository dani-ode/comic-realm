<?php

namespace App\Http\Controllers\Cart;

use App\Domain\Cart\Actions\AddToCart;
use App\Domain\Cart\Actions\ClearCart;
use App\Domain\Cart\Actions\RemoveFromCart;
use App\Domain\Cart\Models\Cart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use InvalidArgumentException;

class CartController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $cart = Cart::with(['items.chapter.comic'])
            ->where('user_id', $request->user()->id)
            ->first();

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
        ]);
    }

    public function getSummary(Request $request): JsonResponse
    {
        $cart = Cart::with(['items.chapter.comic'])
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'cart' => $cart,
            'item_count' => $cart ? $cart->items->count() : 0,
        ]);
    }

    public function store(AddToCartRequest $request, AddToCart $addToCart): JsonResponse|RedirectResponse
    {
        try {
            $cart = $addToCart->execute($request->user(), $request->toDTO());

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'cart' => $cart,
                    'message' => 'Bab berhasil ditambahkan ke keranjang belanja.',
                ]);
            }

            return redirect()->back()->with('success', 'Bab berhasil ditambahkan ke keranjang belanja.');
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(int $chapterId, Request $request, RemoveFromCart $removeFromCart): JsonResponse
    {
        $cart = $removeFromCart->execute($request->user(), $chapterId);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'message' => 'Item berhasil dihapus dari keranjang belanja.',
        ]);
    }

    public function clear(Request $request, ClearCart $clearCart): JsonResponse
    {
        $clearCart->execute($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Keranjang belanja berhasil dikosongkan.',
        ]);
    }
}

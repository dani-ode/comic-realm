<?php

namespace App\Domain\Order\Actions;

use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Order\Services\OrderNumberGenerator;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateOrderFromCart
{
    public function __construct(
        protected OrderNumberGenerator $numberGenerator
    ) {}

    public function execute(User $user): Order
    {
        return DB::transaction(function () use ($user) {
            $cart = Cart::with(['items.chapter.comic'])
                ->where('user_id', $user->id)
                ->first();

            if (! $cart || $cart->items->isEmpty()) {
                throw new InvalidArgumentException('Keranjang belanja Anda kosong.');
            }

            $orderNumber = $this->numberGenerator->generate();
            $subtotal = $cart->total_amount;

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'fee_amount' => 0,
                'total_amount' => $subtotal,
                'status' => 'pending',
                'expired_at' => now()->addHours(24),
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'comic_id' => $cartItem->chapter->comic_id,
                    'chapter_id' => $cartItem->chapter_id,
                    'title_snapshot' => $cartItem->chapter->comic->title . ' - ' . $cartItem->chapter->title,
                    'chapter_number_snapshot' => $cartItem->chapter->chapter_number,
                    'price' => $cartItem->price,
                ]);
            }

            // Clear Cart after Order Created
            $cart->items()->delete();
            $cart->update(['total_amount' => 0]);

            return $order->load(['items.comic', 'items.chapter']);
        });
    }
}

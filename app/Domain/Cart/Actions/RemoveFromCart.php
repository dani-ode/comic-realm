<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveFromCart
{
    public function execute(User $user, int $chapterId): Cart
    {
        return DB::transaction(function () use ($user, $chapterId) {
            $cart = Cart::where('user_id', $user->id)->firstOrFail();

            CartItem::where('cart_id', $cart->id)
                ->where('chapter_id', $chapterId)
                ->delete();

            $cart->recalculateTotal();

            return $cart->load(['items.chapter.comic']);
        });
    }
}

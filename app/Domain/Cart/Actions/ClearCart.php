<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Models\Cart;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class ClearCart
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                $cart->items()->delete();
                $cart->update(['total_amount' => 0]);
            }
        });
    }
}
